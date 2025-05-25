<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Collection;        // Required for ToCollection
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Required for manual validation within collection()
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection; // <--- USE THIS
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation; // For basic rules applied by Maatwebsite before collection()
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // To collect failures from WithValidation's rules()
use Maatwebsite\Excel\Validators\Failure;      // To create custom Failure objects
use Maatwebsite\Excel\Concerns\Importable;     // Provides getFailures() which collects from SkipsOnFailure

class UsersImport implements
    ToCollection,       // <--- Key change: Use ToCollection
    WithHeadingRow,
    WithValidation,     // For initial per-row validation by Maatwebsite/Excel
    SkipsOnFailure      // To collect failures from WithValidation's rules() method
{
    use Importable; // Provides $this->failures property and getFailures() method from SkipsOnFailure

    private $allowedRoles = ['student', 'hod', 'admin', 'dsa', 'sso', 'superadmin'];
    private $customFailures = []; // To store our more complex validation failures
    private $importedRowCount = 0;

    /**
    * This method receives a collection of all rows from the sheet.
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        // --- Pass 1: Analyze the entire import batch for internal conflicts ---
        $hodAssignmentsInFile = []; // [department_id => [excel_row_num1, excel_row_num2]]
        $dsaAssignmentsInFile = []; // [excel_row_num1, excel_row_num2]
        $emailOccurrencesInFile = []; // [email => [excel_row_num1, excel_row_num2]] for intra-file email uniqueness

        foreach ($rows as $excelIndex => $row) {
            $currentExcelRowNum = $excelIndex + 2; // +1 for 0-based index, +1 because heading row is skipped
            $role = strtolower(trim($row['role'] ?? ''));
            $departmentId = trim($row['department_id'] ?? null);
            $email = strtolower(trim($row['email'] ?? ''));

            // Check for HOD assignments within the file
            if ($role === 'hod' && !empty($departmentId) && is_numeric($departmentId)) {
                if (!isset($hodAssignmentsInFile[$departmentId])) {
                    $hodAssignmentsInFile[$departmentId] = [];
                }
                $hodAssignmentsInFile[$departmentId][] = $currentExcelRowNum;
            }

            // Check for DSA assignments within the file
            if ($role === 'dsa') {
                $dsaAssignmentsInFile[] = $currentExcelRowNum;
            }

            // Check for email uniqueness within the file
            if (!empty($email)) {
                if (!isset($emailOccurrencesInFile[$email])) {
                    $emailOccurrencesInFile[$email] = [];
                }
                $emailOccurrencesInFile[$email][] = $currentExcelRowNum;
            }
        }

        // Add failures if multiple HODs for the same department *within this file*
        foreach ($hodAssignmentsInFile as $deptId => $rowNumbers) {
            if (count($rowNumbers) > 1) {
                $this->customFailures[] = new Failure(
                    min($rowNumbers), // Report on the first occurrence
                    'role',
                    ['department_id' => $deptId, 'role' => 'hod'], // Values that caused the failure
                    ["Multiple HODs are assigned to department ID '{$deptId}' within this import file (Excel rows: " . implode(', ', $rowNumbers) . ")."]
                );
            }
        }

        // Add failure if multiple DSAs *within this file*
        if (count($dsaAssignmentsInFile) > 1) {
            $this->customFailures[] = new Failure(
                min($dsaAssignmentsInFile),
                'role',
                ['role' => 'dsa'],
                ["Multiple DSAs are specified within this import file (Excel rows: " . implode(', ', $dsaAssignmentsInFile) . "). Only one DSA is allowed in the system."]
            );
        }

        // Add failures for duplicate emails *within this file*
        foreach ($emailOccurrencesInFile as $email => $rowNumbers) {
            if (count($rowNumbers) > 1) {
                $this->customFailures[] = new Failure(
                    min($rowNumbers),
                    'email',
                    ['email' => $email],
                    ["The email '{$email}' is duplicated within this import file (Excel rows: " . implode(', ', $rowNumbers) . ")."]
                );
            }
        }

        // --- Pass 2: Process each row for creation, including DB checks ---
        foreach ($rows as $excelIndex => $row) {
            $currentExcelRowNum = $excelIndex + 2;
            $rowData = $row->toArray(); // Work with array for Validator

            // A. Basic individual row validation (required fields, formats, student-specific, etc.)
            // This also re-checks email uniqueness against DB for this specific row.
            $validator = Validator::make($rowData, $this->individualRowRules($rowData), $this->customValidationMessages());

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $attribute => $messages) {
                    $this->customFailures[] = new Failure($currentExcelRowNum, $attribute, $rowData, $messages);
                }
                continue; // Skip this row due to basic validation failure
            }

            // B. HOD/DSA specific validation (DB checks and re-confirming file context)
            $role = strtolower(trim($rowData['role'] ?? ''));
            $departmentId = trim($rowData['department_id'] ?? null);
            $email = strtolower(trim($rowData['email'] ?? ''));
            $canProceedWithRow = true; // Flag to track if this row is still valid

            // Skip if this row's HOD assignment was part of an intra-file conflict already logged
            if ($role === 'hod' && isset($hodAssignmentsInFile[$departmentId]) && count($hodAssignmentsInFile[$departmentId]) > 1) {
                $canProceedWithRow = false; // Error already logged from Pass 1
            }
            // Skip if this row's DSA assignment was part of an intra-file conflict already logged
            if ($role === 'dsa' && count($dsaAssignmentsInFile) > 1) {
                $canProceedWithRow = false; // Error already logged from Pass 1
            }
            // Skip if this row's email was part of an intra-file conflict already logged
            if (isset($emailOccurrencesInFile[$email]) && count($emailOccurrencesInFile[$email]) > 1) {
                $canProceedWithRow = false; // Error already logged from Pass 1
            }

            if (!$canProceedWithRow) {
                continue; // Skip to next row as this row has an intra-file conflict logged in Pass 1
            }

            // Now, check against the database if there were no intra-file conflicts for this specific row.
            if ($role === 'hod') {
                if (!empty($departmentId) && is_numeric($departmentId)) {
                    $existingDbHod = User::where('role', 'hod')->where('department_id', $departmentId)->exists();
                    if ($existingDbHod) {
                        $this->customFailures[] = new Failure($currentExcelRowNum, 'role', $rowData, ["An HOD already exists in the database for department ID '{$departmentId}'."]);
                        $canProceedWithRow = false;
                    }
                } else { // Should be caught by individualRowRules, but defensive check.
                    $this->customFailures[] = new Failure($currentExcelRowNum, 'department_id', $rowData, ["Department ID is missing or invalid for HOD role."]);
                    $canProceedWithRow = false;
                }
            }

            if ($role === 'dsa') {
                $existingDbDsa = User::where('role', 'dsa')->exists();
                if ($existingDbDsa) {
                    $this->customFailures[] = new Failure($currentExcelRowNum, 'role', $rowData, ["A DSA already exists in the database."]);
                    $canProceedWithRow = false;
                }
            }

            if (!$canProceedWithRow) {
                continue; // Skip this row due to DB conflict or other issue found in Pass 2
            }

            // C. Create User if all checks passed for this row
            try {
                User::create([
                    'name'     => trim($rowData['name']),
                    'email'    => $email, // Use the validated and trimmed email
                    'password' => Hash::make($rowData['password'] ?? 'password123'), // Default if not set
                    'role'     => $role,
                    'department_id' => $departmentId, // Already validated to exist and be numeric
                    'program'  => ($role === 'student' && isset($rowData['program'])) ? trim($rowData['program']) : null,
                    'class'    => ($role === 'student' && isset($rowData['class'])) ? trim($rowData['class']) : null,
                    'email_verified_at' => now(), // Or handle verification differently if needed
                ]);
                $this->importedRowCount++;
            } catch (\Illuminate\Database\QueryException $e) {
                // Catch potential unique constraint violations (e.g. email) not caught by initial rule
                // This can happen due to race conditions or case sensitivity differences between PHP validation and DB collation.
                $this->customFailures[] = new Failure($currentExcelRowNum, 'email', $rowData, ["Database error creating user with email '{$email}': Likely a duplicate email. Details: " . ($e->errorInfo[2] ?? $e->getMessage())]);
                Log::error("DB error during user creation in import for email {$email}: " . $e->getMessage(), $rowData);
            } catch (\Exception $e) {
                $this->customFailures[] = new Failure($currentExcelRowNum, 'general', $rowData, ["An unexpected error occurred creating user with email '{$email}': " . $e->getMessage()]);
                Log::error("Unexpected error during user creation in import for email {$email}: " . $e->getMessage(), $rowData);
            }
        }
    }

    /**
     * These rules are applied by WithValidation *before* the `collection` method.
     * Good for catching simple errors early (e.g., department_id existence).
     * Note: '*.email' unique rule here checks against DB for all emails in sheet.
     * Intra-file email uniqueness is handled in collection() for more precise error reporting.
     */
    public function rules(): array
    {
        return [
            // Apply to each row in the collection
            '*.name'      => ['required', 'string', 'max:255'],
            '*.email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')], // Checks against DB
            '*.password'  => ['nullable', 'string', 'min:8'],
            '*.role'      => ['required', 'string', Rule::in($this->allowedRoles)],
            '*.department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            '*.program'   => ['nullable', 'string', 'max:255'], // Conditional handled in individualRowRules
            '*.class'     => ['nullable', 'string', 'max:255'],  // Conditional handled in individualRowRules
        ];
    }

    /**
     * Validation rules for a single row, used by the manual Validator in `collection()`.
     */
    public function individualRowRules(array $rowData): array
    {
        $role = strtolower(trim($rowData['role'] ?? ''));
        return [
            'name'      => ['required', 'string', 'max:255'],
            // Email uniqueness against DB is re-checked here for this specific row.
            // Intra-file email uniqueness is handled prior to this in collection() method.
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['required', 'string', Rule::in($this->allowedRoles)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'program'   => ['nullable', 'string', 'max:255', Rule::requiredIf($role === 'student')],
            'class'     => ['nullable', 'string', 'max:255', Rule::requiredIf($role === 'student')],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.email.unique' => 'The email :input (from Excel) already exists in the database.', // For WithValidation rules()
            'email.unique'   => 'The email :input already exists in the database.', // For individualRowRules()
            '*.department_id.exists' => 'The department ID :input (from Excel) does not exist.',
            'department_id.exists' => 'The department ID :input does not exist.',
            'program.required_if' => 'The program field is required when role is student.',
            'class.required_if' => 'The class field is required when role is student.',
            // Add other general messages
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'role.required' => 'Role is required.',
            'department_id.required' => 'Department ID is required.',
        ];
    }

    /**
     * Collects failures from the main `rules()` method (WithValidation).
     * These are failures Maatwebsite/Excel catches before collection() is even called.
     */
    public function onFailure(Failure ...$failures)
    {
        // `Importable` trait provides `$this->failures` (which is an array).
        // We merge these pre-collection failures into our customFailures list.
        $this->customFailures = array_merge($this->customFailures, $failures);
    }

    /**
     * Getter for all collected validation failures (both from WithValidation and custom checks).
     */
    public function getValidationFailures(): array
    {
        return $this->customFailures;
    }

    /**
     * Getter for successfully imported rows.
     */
    public function getImportedRowCount(): int
    {
        return $this->importedRowCount;
    }
}
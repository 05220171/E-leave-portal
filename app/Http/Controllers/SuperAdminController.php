<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel; // Import Excel Facade
use App\Imports\UsersImport;        // Import your UsersImport class
use Illuminate\Support\Facades\DB;   // For database transactions
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException; // To catch Excel validation exceptions
use Illuminate\Support\Facades\Log; // For logging general exceptions
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Http\Requests\StoreUserRequest; // <--- This line IMPORTS the class that DOES the validation
use App\Http\Requests\UpdateUserRequest; // <--- This line IMPORTS the class that DOES the validation

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $userCount = User::count();
        $departmentCount = Department::count();
        return view('superadmin.dashboard', compact('userCount', 'departmentCount'));
    }

    public function index()
    {
        $users = User::with('department')->latest()->paginate(15);
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(); // Fetch all departments, optionally order them
        // No $programOptions or $classOptions are passed here for AJAX dependent dropdowns
        return view('superadmin.create-user', compact('departments'));
    }


    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'program' => $validated['role'] === 'student' ? $validated['program'] : null, // 'program' here will be the program CODE
            'class' => $validated['role'] === 'student' ? $validated['class'] : null,     // 'class' here will be the class CODE
            'email_verified_at' => now(),
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user) // User $user for route model binding
    {
        $departments = Department::all();
        $assignableSystemRoles = ['dsa', 'sso', 'daa', 'president', 'admin', 'superadmin'];
        $departmentSpecificRoles = ['hod', 'student'];

        return view('superadmin.edit-user', compact('user', 'departments', 'assignableSystemRoles', 'departmentSpecificRoles'));
    }


    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'program' => $validated['role'] === 'student' ? $validated['program'] : null,
            'class' => $validated['role'] === 'student' ? $validated['class'] : null,
        ];

        if (isset($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for importing users.
     */
    public function importForm()
    {
        return view('superadmin.users.import-form');
    }

    /**
     * Handle the import of users from an Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        $file = $request->file('file');
        $usersImport = new UsersImport();

        DB::beginTransaction();
        try {
            Excel::import($usersImport, $file);
            $importFailures = $usersImport->getValidationFailures();
            $importedRowCount = $usersImport->getImportedRowCount();

            if (!empty($importFailures)) {
                DB::rollBack();
                $errorMessages = $this->formatImportFailures($importFailures);
                Log::warning('User Import Validation Failures (Collected by UsersImport): ', $errorMessages);
                return redirect()->route('superadmin.users.importForm')
                                 ->with('error', 'No users were imported. Please correct the issues listed below and try again.')
                                 ->with('import_errors', $errorMessages);
            }
            DB::commit();
            if ($importedRowCount > 0) {
                 return redirect()->route('superadmin.users.index')->with('success', "Users import process completed. {$importedRowCount} users were successfully imported.");
            } else {
                 return redirect()->route('superadmin.users.index')->with('info', "Users import process completed. No new users were imported (possibly all rows had issues or the file was empty).");
            }
        } catch (ExcelValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = $this->formatExcelValidationFailures($failures);
            Log::warning('User Import ExcelValidationException (Maatwebsite Rules): ', $errorMessages);
            return redirect()->route('superadmin.users.importForm')
                             ->with('error', 'User import failed due to initial validation errors. Please correct the issues listed below and try again.')
                             ->with('import_errors', $errorMessages);
        } catch (\Maatwebsite\Excel\Exceptions\SheetNotFoundException $e) {
            DB::rollBack();
            Log::error('User Import Sheet Not Found: ' . $e->getMessage());
            return redirect()->route('superadmin.users.importForm')->with('error', 'Import failed: A required sheet was not found in the Excel file.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('General User Import Exception: ' . $e->getMessage(), ['exception' => $e]);
            $errorMessage = 'An unexpected error occurred during user import. Please check the file format or contact support.';
            if (app()->environment('local')) {
                $errorMessage .= ' Details: ' . $e->getMessage();
            }
            return redirect()->route('superadmin.users.importForm')->with('error', $errorMessage);
        }
    }

    private function formatExcelValidationFailures($failures): array
    {
        $errorMessages = [];
        foreach ($failures as $failure) {
            $errorMessages[] = 'Excel Row ' . $failure->row() .
                               ' (Field: ' . $failure->attribute() .
                               ', Value: "' . ($failure->values()[$failure->attribute()] ?? 'N/A') .
                               '") - Errors: ' . implode(', ', $failure->errors());
        }
        return $errorMessages;
    }

    private function formatImportFailures($failures): array
    {
        $errorMessages = [];
        foreach ($failures as $failure) {
             $errorMessage = 'Excel Row ' . $failure->row() .
                            ' (Field: ' . $failure->attribute() . ')';
            $values = $failure->values();
            if (!empty($values) && isset($values[$failure->attribute()])) {
                 $errorMessage .= ' (Value: "' . $values[$failure->attribute()] . '")';
            } elseif (!empty($values) && $failure->attribute() === 'role' && (isset($values['department_id']) || isset($values['role'])) ){
                $context = [];
                if(isset($values['department_id'])) $context[] = "Dept ID: ".$values['department_id'];
                if(isset($values['role'])) $context[] = "Role: ".$values['role'];
                if(!empty($context)) $errorMessage .= ' (Context: ' . implode(', ', $context) . ')';
            }
            $errorMessage .= ' - Errors: ' . implode(', ', $failure->errors());
            $errorMessages[] = $errorMessage;
        }
        return $errorMessages;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $userCount = User::count();
        $departmentCount = Department::count();
        // Add Program count if desired
        $programCount = Program::count();
        return view('superadmin.dashboard', compact('userCount', 'departmentCount', 'programCount'));
    }

    public function index()
    {
        // Eager load program relationship if you want to display program name
        $users = User::with(['department', 'program'])->latest()->paginate(15);
        return view('superadmin.users.index', compact('users'));
    }

    public function manageStudents(Request $request) // <<< ADD Request $request
    {
        $searchTerm = $request->input('search');

        $query = User::where('role', 'student')
                        ->with(['department', 'program']); // Eager load relationships

        if ($searchTerm) {
            $query->where(function ($q_search) use ($searchTerm) {
                $q_search->where('name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                         ->orWhereHas('department', function ($deptQuery) use ($searchTerm) {
                             $deptQuery->where('name', 'LIKE', "%{$searchTerm}%");
                         })
                         ->orWhereHas('program', function ($progQuery) use ($searchTerm) {
                             // Assuming program name or code is what you'd search by
                             $progQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                       ->orWhere('code', 'LIKE', "%{$searchTerm}%");
                         })
                         ->orWhere('class', 'LIKE', "%{$searchTerm}%"); // Assuming 'class' is the class/year string
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        return view('superadmin.users.students', compact('students'));
    }

    public function manageStaffs(Request $request) // Potentially add search here too
    {
        $staffRoles = ['hod', 'dsa', 'sso', 'admin', 'superadmin']; // Include all non-student staff
        $searchTerm = $request->input('search');
    
        $query = User::whereIn('role', $staffRoles)
                      ->with(['department', 'program']); // Staff might also have programs if relevant
    
        if ($searchTerm) {
            $query->where(function ($q_search) use ($searchTerm) {
                $q_search->where('name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('role', 'LIKE', "%{$searchTerm}%") // Search by role string
                         ->orWhereHas('department', function ($deptQuery) use ($searchTerm) {
                             $deptQuery->where('name', 'LIKE', "%{$searchTerm}%");
                         });
            });
        }
    
        $staffs = $query->latest()->paginate(15)->withQueryString();
        return view('superadmin.users.staff', compact('staffs'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('superadmin.create-user', compact('departments'));
    }

    public function store(StoreUserRequest $request) // Uses StoreUserRequest for validation
    {
        $validated = $request->validated(); // Get validated data

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null, // department_id is now conditional in FormRequest
            'program_id'    => ($validated['role'] === 'student' && isset($validated['program_id'])) ? $validated['program_id'] : null, // NEW: use program_id
            'class'         => ($validated['role'] === 'student' && isset($validated['class'])) ? $validated['class'] : null, // Assuming class is still a string for now
            'email_verified_at' => now(), // Good to set this
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        // These role lists might be useful if you build a more dynamic role assignment in edit form
        $assignableSystemRoles = ['dsa', 'sso', 'admin', 'superadmin'];
        $departmentSpecificRoles = ['hod', 'student'];

        return view('superadmin.edit-user', compact('user', 'departments', 'assignableSystemRoles', 'departmentSpecificRoles'));
    }

    public function update(UpdateUserRequest $request, User $user) // Uses UpdateUserRequest
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'program_id'    => ($validated['role'] === 'student' && isset($validated['program_id'])) ? $validated['program_id'] : null, // NEW
            'class'         => ($validated['role'] === 'student' && isset($validated['class'])) ? $validated['class'] : null,
        ];

        if ($request->filled('password') && !empty($validated['password'])) {
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

    public function importForm()
    {
        return view('superadmin.users.import-form');
    }

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
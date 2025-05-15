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
        $departments = Department::all();
        return view('superadmin.create-user', compact('departments'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,student,hod,dsa,sso,superadmin', // Added superadmin
            'department_id' => 'required|exists:departments,id',
        ];

        // Assuming 'class' is the form field name for class/year
        if ($request->role === 'student') {
            $rules['program'] = 'required|string|max:255';
            $rules['class'] = 'required|string|max:255'; // Changed from class_year to class
        }

        $validated = $request->validate($rules);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'program' => $validated['role'] === 'student' ? $validated['program'] : null,
            'class' => $validated['role'] === 'student' ? $validated['class'] : null, // Changed from class_year
        ]);

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('superadmin.edit-user', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,student,hod,dsa,sso,superadmin', // Added superadmin
            'department_id' => 'required|exists:departments,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'sometimes|string|min:8|confirmed';
        }

        if ($request->role === 'student') {
            $rules['program'] = 'required|string|max:255';
            $rules['class'] = 'required|string|max:255'; // Changed from class_year
        }

        $validated = $request->validate($rules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'program' => $validated['role'] === 'student' ? $validated['program'] : null,
            'class' => $validated['role'] === 'student' ? $validated['class'] : null, // Changed from class_year
        ];

        if ($request->filled('password')) {
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
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // Max 10MB, adjust as needed
        ]);

        $file = $request->file('file');
        // Note: No need to pass any arguments to UsersImport constructor unless you specifically designed it that way
        $usersImport = new UsersImport();

        DB::beginTransaction();

        try {
            Excel::import($usersImport, $file);

            // If UsersImport uses SkipsOnError, errors for individual rows are handled by its onError() method (e.g., logged).
            // The import process itself will complete here unless a more critical, unskippable error occurs.
            // If you need to collect and display skipped row errors, you'd implement a collector in UsersImport
            // and retrieve it here. For now, a general success message is good if SkipsOnError is used.

            DB::commit();
            return redirect()->route('superadmin.users.index')->with('success', 'Users import process completed. Check logs for details on any skipped rows if SkipsOnError was used.');

        } catch (ExcelValidationException $e) {
            DB::rollBack();
            $failures = $e->failures(); // This gets specific row validation failures
            $errorMessages = [];
            foreach ($failures as $failure) {
                // Construct a more detailed error message
                $errorMessages[] = 'Error on Excel row ' . $failure->row() .
                                   ' for attribute "' . $failure->attribute() .
                                   '" with value "' . ($failure->values()[$failure->attribute()] ?? 'N/A') .
                                   '". Errors: ' . implode(', ', $failure->errors());
            }
            Log::warning('User Import Validation Failures: ', $errorMessages); // Log for admin reference
            return redirect()->route('superadmin.users.importForm')
                             ->with('error', 'User import failed due to validation errors. Please correct the issues listed below and try again.')
                             ->with('import_errors', $errorMessages); // Pass errors to the view
        } catch (\Maatwebsite\Excel\Exceptions\SheetNotFoundException $e) {
            DB::rollBack();
            Log::error('User Import Sheet Not Found: ' . $e->getMessage());
            return redirect()->route('superadmin.users.importForm')->with('error', 'Import failed: A required sheet was not found in the Excel file.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the full exception for detailed debugging
            Log::error('General User Import Exception: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('superadmin.users.importForm')->with('error', 'An unexpected error occurred during user import. Please check the file format or contact support. Details have been logged.');
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User; // <--- ADD THIS: Import the User model
use Illuminate\Support\Facades\Log; // <--- ADD THIS: For logging errors

class DepartmentController extends Controller
{
    public function index()
    {
        // Optional: Eager load user count for display in the index view
        // $departments = Department::withCount('users')->latest()->paginate(10); // Example with pagination
        $departments = Department::withCount('users')->latest()->get(); // Or get all
        return view('superadmin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('superadmin.create-department');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            // Add other validation rules for department fields if any
        ]);

        Department::create($validated); // Use validated data

        return redirect()->route('superadmin.departments.index')
                         ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department) // Route model binding
    {
        return view('superadmin.edit-department', compact('department'));
    }

    public function update(Request $request, Department $department) // Route model binding
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            // Add other validation rules for department fields if any
        ]);

        $department->update($validated); // Use validated data

        return redirect()->route('superadmin.departments.index')
                         ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     * Prevents deletion if users are assigned to it.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Department $department) // Route model binding
    {
        // Check if any users are currently assigned to this department
        // This relies on the `users()` relationship defined in your Department model
        if ($department->users()->exists()) {
            // If users are assigned, prevent deletion and redirect with an error message
            return redirect()->route('superadmin.departments.index')
                             ->with('error', 'Cannot delete department: "' . $department->name . '". Users are still assigned to it. Please reassign or remove users from this department first.');
        }

        // If no users are assigned, proceed with the deletion
        try {
            $department->delete();
            return redirect()->route('superadmin.departments.index')
                             ->with('success', 'Department: "' . $department->name . '" deleted successfully.');
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error("Error deleting department ID {$department->id} (Name: {$department->name}): " . $e->getMessage());

            // Redirect back with a generic error message
            return redirect()->route('superadmin.departments.index')
                             ->with('error', 'Could not delete department. An unexpected error occurred. Please try again or contact support if the issue persists.');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // For unique validation on update

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the leave types.
     */
    public function index()
    {
        $leaveTypes = LeaveType::latest()->paginate(10); // Or however many you want per page
        return view('superadmin.leave_types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave type.
     */
    public function create()
    {
        return view('superadmin.leave_types.create');
    }

    /**
     * Store a newly created leave type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean', // 'sometimes' if checkbox might not be sent
        ]);

        // Handle checkbox value if not sent
        $validated['is_active'] = $request->has('is_active');

        LeaveType::create($validated);

        return redirect()->route('superadmin.leave-types.index')
                         ->with('success', 'Leave Type created successfully.');
    }

    /**
     * Show the form for editing the specified leave type.
     */
    public function edit(LeaveType $leaveType) // Route model binding
    {
        return view('superadmin.leave_types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types', 'name')->ignore($leaveType->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $leaveType->update($validated);

        return redirect()->route('superadmin.leave-types.index')
                         ->with('success', 'Leave Type updated successfully.');
    }

    /**
     * Remove the specified leave type from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Add check here if leave type is in use before deleting if necessary
        // For example: if ($leaveType->leaves()->count() > 0 || $leaveType->leaveWorkflows()->count() > 0) {
        // return redirect()->route('superadmin.leave-types.index')->with('error', 'Cannot delete. Leave Type is in use.');
        // }

        try {
            $leaveType->delete();
            return redirect()->route('superadmin.leave-types.index')
                             ->with('success', 'Leave Type deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation if any
            return redirect()->route('superadmin.leave-types.index')
                             ->with('error', 'Cannot delete leave type. It might be in use.');
        }
    }
}
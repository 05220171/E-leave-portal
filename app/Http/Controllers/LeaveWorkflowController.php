<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\LeaveWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveWorkflowController extends Controller
{
    // Define the roles that can be part of a workflow
    protected $assignableRoles = ['hod', 'dsa', 'sso'];
    // Define the possible action types for a step
    protected $actionTypes = ['approval', 'record_keeping'];


    /**
     * Display the workflow management page for a specific leave type.
     * This will list existing steps and show a form to add a new one.
     */
    public function index(LeaveType $leaveType) // Route model binding for LeaveType
    {
        // Eager load workflows to prevent N+1 issues if you iterate over them
        $leaveType->load('leaveWorkflows');

        return view('superadmin.leave_workflows.index', [
            'leaveType' => $leaveType,
            'workflows' => $leaveType->leaveWorkflows, // Already ordered by step_number due to model relationship
            'assignableRoles' => $this->assignableRoles,
            'actionTypes' => $this->actionTypes,
        ]);
    }

    /**
     * Store a newly created workflow step in storage.
     */
    public function store(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'approver_role' => [
                'required',
                Rule::in($this->assignableRoles),
                // Ensure this role is not already in this leave type's workflow
                Rule::unique('leave_workflows')->where(function ($query) use ($leaveType) {
                    return $query->where('leave_type_id', $leaveType->id);
                }),
            ],
            'action_type' => ['required', Rule::in($this->actionTypes)],
        ]);

        // Determine the next step number
        $nextStepNumber = ($leaveType->leaveWorkflows()->max('step_number') ?? 0) + 1;

        $leaveType->leaveWorkflows()->create([
            'approver_role' => $validated['approver_role'],
            'action_type' => $validated['action_type'],
            'step_number' => $nextStepNumber,
        ]);

        return redirect()->route('superadmin.leave-types.workflows.index', $leaveType->id)
                         ->with('success', 'Workflow step added successfully.');
    }

    /**
     * Remove the specified workflow step from storage.
     */
    public function destroy(LeaveType $leaveType, LeaveWorkflow $workflow) // Route model binding for both
    {
        // Ensure the workflow step actually belongs to the given leave type
        // (Though route model binding with nested resources usually handles this)
        if ($workflow->leave_type_id !== $leaveType->id) {
            abort(404);
        }

        $deletedStepNumber = $workflow->step_number;
        $workflow->delete();

        // Re-sequence subsequent steps for this leave type
        LeaveWorkflow::where('leave_type_id', $leaveType->id)
                     ->where('step_number', '>', $deletedStepNumber)
                     ->decrement('step_number');

        return redirect()->route('superadmin.leave-types.workflows.index', $leaveType->id)
                         ->with('success', 'Workflow step deleted successfully.');
    }

    // OPTIONAL: Edit method for workflow steps (can be added later)
    /*
    public function edit(LeaveType $leaveType, LeaveWorkflow $workflow)
    {
        return view('superadmin.leave_workflows.edit', [
            'leaveType' => $leaveType,
            'workflow' => $workflow,
            'assignableRoles' => $this->assignableRoles,
            'actionTypes' => $this->actionTypes,
        ]);
    }

    public function update(Request $request, LeaveType $leaveType, LeaveWorkflow $workflow)
    {
        $validated = $request->validate([
            'approver_role' => [
                'required',
                Rule::in($this->assignableRoles),
                Rule::unique('leave_workflows')->where(function ($query) use ($leaveType, $workflow) {
                    return $query->where('leave_type_id', $leaveType->id)->where('id', '!=', $workflow->id);
                }),
            ],
            'action_type' => ['required', Rule::in($this->actionTypes)],
            // 'step_number' could be updated too if re-ordering is implemented
        ]);

        $workflow->update($validated);

        return redirect()->route('superadmin.leave-types.workflows.index', $leaveType->id)
                         ->with('success', 'Workflow step updated successfully.');
    }
    */
}
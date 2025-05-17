<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval; // <<< ADD THIS
use App\Models\LeaveWorkflow;       // <<< ADD THIS
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;       // <<< ADD THIS for transactions
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// TODO: Later: use App\Notifications\LeaveApprovedByHodNotification;
// TODO: Later: use App\Notifications\LeaveRejectedByHodNotification;
// TODO: Later: use App\Notifications\LeaveForwardedToNextApproverNotification;


class HodController extends Controller
{
    public function index()
    {
        $hodUser = Auth::user();
        if (!$hodUser || !$hodUser->department_id) {
            return view('hod.dashboard', ['leaves' => collect()])
                     ->with('error', 'Unable to determine your department or you are not properly configured as an HOD.');
        }
        $departmentId = $hodUser->department_id;
        $leaves = Leave::with(['student.department', 'type'])
            ->where('current_approver_role', 'hod')
            ->where('overall_status', 'awaiting_hod_approval')
            ->whereHas('student', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15); // Changed to paginate

        return view('hod.dashboard', compact('leaves', 'hodUser'));
    }

    public function approve(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::with('type.leaveWorkflows')->findOrFail($id); // Eager load workflow

        // Security Check: Ensure this HOD is the current approver and it's their department
        if ($leave->current_approver_role !== 'hod' || $leave->overall_status !== 'awaiting_hod_approval' || $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        DB::beginTransaction();
        try {
            // 1. Record HOD's approval action
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $request->input('approval_remarks'), // If you add an approval remarks field
                'action_at' => now(),
            ]);

            // 2. Determine next step in the workflow
            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows // Access eager loaded workflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->first(); // Gets the next step

            if ($nextWorkflowStep) {
                // There is a next step
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;
                $statusSuffix = ($nextWorkflowStep->action_type === 'record_keeping') ? '_record_keeping' : '_approval';
                $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . $statusSuffix;
                $leave->save();

                DB::commit();
                // TODO: Email student: "Your leave approved by HOD, forwarded to [Next Approver Role]"
                // TODO: Email next approver: "[Student Name]'s leave request awaits your action"
                return redirect()->route('hod.dashboard')->with('success', 'Leave approved by HOD and forwarded to ' . Str::title($nextWorkflowStep->approver_role) . '.');
            } else {
                // No more steps - HOD was the final decider (unlikely if workflow usually includes DSA/SSO)
                // This branch should be rare if workflows are well-defined to include other roles
                $leave->overall_status = 'approved'; // Final approval
                $leave->current_approver_role = null; // No one else to approve
                // current_step_number can remain as the last step number
                $leave->save();

                DB::commit();
                // TODO: Email student: "Your leave has been fully approved."
                return redirect()->route('hod.dashboard')->with('success', 'Leave approved by HOD (Final Approval).');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Approve Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while approving the leave. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::findOrFail($id); // No need to eager load workflow for rejection

        // Security Check
        if ($leave->current_approver_role !== 'hod' || $leave->overall_status !== 'awaiting_hod_approval' || $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // 1. Record HOD's rejection action
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'rejected',
                'remarks' => $request->input('remarks'),
                'action_at' => now(),
            ]);

            // 2. Update leave status
            $leave->overall_status = 'rejected_by_hod';
            $leave->remarks = $request->input('remarks'); // Storing final rejection remarks on the leave itself
            $leave->current_approver_role = null; // Workflow stops
            // current_step_number can remain
            $leave->save();

            DB::commit();
            // TODO: Email student: "Your leave request was rejected by HOD. Remarks: [remarks]"
            return redirect()->route('hod.dashboard')->with('success', 'Leave request rejected.'); // Use success as action was successful
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Reject Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while rejecting the leave. Please try again.');
        }
    }

    public function studentHistory()
    {
        // ... (this method can largely remain the same, but ensure its view uses overall_status)
        $hod = Auth::user();
        if (!$hod || !$hod->department_id) {
            return redirect()->route('hod.dashboard')
                             ->with('error', 'Unable to determine your department to view history.');
        }
        $departmentId = $hod->department_id;

        $leavesHistory = Leave::with(['student', 'type', 'approvalActions.user']) // Eager load more details
            ->whereHas('student', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('hod.student-history', compact('leavesHistory'));
    }
}
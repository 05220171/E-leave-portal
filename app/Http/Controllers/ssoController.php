<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval; // <<< ADD THIS
use App\Models\User;                // <<< ADD THIS (if needed for user details)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;       // <<< ADD THIS
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// Remove old notification 'use' statements if not used for new purpose
// use App\Notifications\LeaveStatusUpdatedNotification;

class SSOController extends Controller
{
    /**
     * Display leaves for SSO to review/record.
     * This could be leaves explicitly assigned to SSO for record-keeping OR all approved leaves.
     */
    public function dashboard()
    {
        $ssoUser = Auth::user();

        // Option A: Show leaves specifically assigned to SSO for record-keeping via workflow
        // $leavesForRecord = Leave::with(['student.department', 'type', 'approvalActions'])
        //     ->where('current_approver_role', 'sso')
        //     // The status might be 'approved' (set by DSA) or a specific 'awaiting_sso_record_keeping'
        //     // If DSA sets it to 'approved' directly, then current_approver_role='sso' is the key.
        //     ->where(function($query) {
        //         $query->where('overall_status', 'awaiting_sso_record_keeping') // If DSA sets this status
        //               ->orWhere('overall_status', 'approved'); // If DSA sets to approved but current_approver is SSO
        //     })
        //     ->orderBy('updated_at', 'desc') // Show most recently updated ones first
        //     ->paginate(15);

        // Option B: Show ALL leaves that are fully approved (simpler if SSO just needs a view of all approved)
        $approvedLeaves = Leave::with(['student.department', 'type', 'approvalActions'])
                    ->where('overall_status', 'approved')
                    ->orderBy('updated_at', 'desc')
                    ->paginate(15);
        $leavesForRecord = $approvedLeaves; // Use this if Option B is preferred

        return view('sso.dashboard', compact('leavesForRecord', 'ssoUser'));
    }

    /**
     * Mark a leave request as recorded by the SSO.
     */
    public function markAsRecorded(Request $request, Leave $leave) // Route model binding for Leave
    {
        $ssoUser = Auth::user();

        // Ensure the leave is in a state where SSO can record it
        // (e.g., current_approver_role is 'sso' OR overall_status is 'approved' and not yet recorded by SSO)
        $isAssignedToSsoForRecord = ($leave->current_approver_role === 'sso' &&
                                     ($leave->overall_status === 'awaiting_sso_record_keeping' || $leave->overall_status === 'approved'));

        // Alternative check: if the leave is 'approved' and hasn't been recorded by SSO yet
        $isApprovedAndNotYetRecordedBySso = ($leave->overall_status === 'approved' &&
                                           !$leave->approvalActions()->where('acted_as_role', 'sso')->where('action_taken', 'recorded')->exists());


        if (!$isAssignedToSsoForRecord && !$isApprovedAndNotYetRecordedBySso) {
             return redirect()->route('sso.dashboard')->with('error', 'This leave request is not in a state to be marked as recorded by you, or has already been recorded.');
        }

        // Check if SSO already recorded this leave to prevent duplicate actions
        if ($leave->approvalActions()->where('user_id', $ssoUser->id)->where('acted_as_role', 'sso')->where('action_taken', 'recorded')->exists()) {
            return redirect()->route('sso.dashboard')->with('info', 'You have already recorded this leave request.');
        }


        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $ssoUser->id,
                'acted_as_role' => 'sso',
                // Use the leave's current_step_number if it was explicitly assigned to SSO via workflow.
                // If SSO is just viewing all 'approved' leaves, this step number might be the last one DSA acted on,
                // or you might define a conceptual "final recording step" number.
                'workflow_step_number' => $leave->current_step_number ?? 99, // 99 as a placeholder for generic recording step
                'action_taken' => 'recorded',
                'remarks' => $request->input('sso_remarks'), // If SSO can add remarks
                'action_at' => now(),
            ]);

            // Update the leave record if it was specifically pending SSO
            if ($leave->current_approver_role === 'sso') {
                $leave->current_approver_role = null; // Process complete from workflow perspective
                // overall_status should already be 'approved' or 'awaiting_sso_record_keeping'
                // If it was 'awaiting_sso_record_keeping', you might change it to 'approved_and_recorded'
                if ($leave->overall_status === 'awaiting_sso_record_keeping') {
                    $leave->overall_status = 'approved_recorded'; // Or 'approved_recorded'
                }
                $leave->save();
            }

            DB::commit();

            // No email typically needed from SSO's "mark as recorded" action,
            // as the main approval notifications were sent by DSA.
            // Unless there's a specific internal notification required.

            return redirect()->route('sso.dashboard')->with('success', 'Leave request for ' . $leave->student->name . ' marked as recorded.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SSO Mark as Recorded Error for Leave ID {$leave->id}: " . $e->getMessage());
            return redirect()->route('sso.dashboard')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Remove or comment out old approveLeave and rejectLeave methods
    /*
    public function approveLeave(Request $request, $id) { ... }
    public function rejectLeave(Request $request, $id) { ... }
    */
}
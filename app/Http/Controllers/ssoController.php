<?php

namespace App\Http\Controllers;

use App\Models\Leave;
//use App\Models\LeaveRequestApproval; // <<< ADD THIS
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
    public function dashboard(Request $request)
    {
        $ssoUser = Auth::user();
        $userName = $ssoUser->name;
        $role = $ssoUser->role;

        $searchTerm = $request->input('search');

        // SSO sees leaves that are fully approved or were explicitly sent for their record keeping
        $query = Leave::with([
                        'student.department',
                        'student.program', // Eager load student's program
                        'type',
                        // 'approvalActions.user' // Only needed if displaying who recorded, which we are removing
                     ])
                     ->whereIn('overall_status', ['approved', 'approved_recorded', 'awaiting_sso_record_keeping']); // Leaves SSO should see

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('student', function ($studentQuery) use ($searchTerm) {
                    $studentQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                 ->orWhereHas('department', function ($deptQuery) use ($searchTerm) {
                                     $deptQuery->where('name', 'LIKE', "%{$searchTerm}%");
                                 })
                                 ->orWhereHas('program', function ($progQuery) use ($searchTerm) {
                                     $progQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                               ->orWhere('code', 'LIKE', "%{$searchTerm}%");
                                 });
                })
                ->orWhereHas('type', function ($typeQuery) use ($searchTerm) {
                    $typeQuery->where('name', 'LIKE', "%{$searchTerm}%");
                });
                // You can add date range search here if needed in the future
            });
        }

        // Order by when they were last updated (e.g., when they became 'approved' or 'awaiting_sso_record_keeping')
        $leavesForRecord = $query->orderBy('updated_at', 'desc')
                                 ->paginate(15)
                                 ->withQueryString(); // Appends search query to pagination links

        return view('sso.dashboard', compact('leavesForRecord', 'ssoUser', 'userName', 'role'));
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
                'workflow_step_number' => $leave->current_step_number ?? 99, // 99 as a placeholder for generic recording step
                'action_taken' => 'recorded',
                'remarks' => $request->input('sso_remarks'), // If SSO can add remarks
                'action_at' => now(),
            ]);

            // Update the leave record if it was specifically pending SSO
            if ($leave->current_approver_role === 'sso') {
                $leave->current_approver_role = null; // Process complete from workflow perspective
                if ($leave->overall_status === 'awaiting_sso_record_keeping') {
                    $leave->overall_status = 'approved_recorded'; // Or 'approved_recorded'
                }
                $leave->save();
            }

            DB::commit();

            return redirect()->route('sso.dashboard')->with('success', 'Leave request for ' . $leave->student->name . ' marked as recorded.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SSO Mark as Recorded Error for Leave ID {$leave->id}: " . $e->getMessage());
            return redirect()->route('sso.dashboard')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
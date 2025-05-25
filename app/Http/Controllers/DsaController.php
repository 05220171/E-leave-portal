<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval;
use App\Models\LeaveWorkflow;
use App\Models\User;
use App\Models\LeaveType; // Ensure this is used if you check by name
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Mail\LeaveApprovedFinalToStudent;
use App\Mail\LeaveApprovedToStakeholders;
use App\Mail\LeaveRejectedToStudent;
use App\Mail\LeaveApprovedForYourRecords;
use App\Mail\LeaveApprovedByFirstApproverToStudent; // <<< ADD THIS
use App\Mail\NewLeaveRequestForYourApproval;


class DsaController extends Controller
{
    public function index()
    {
        $dsaUser = Auth::user();
        $leaves = Leave::with(['student.department', 'type'])
            ->where('current_approver_role', 'dsa')
            ->where('overall_status', 'awaiting_dsa_approval')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('dsa.dashboard', compact('leaves', 'dsaUser'));
    }

    public function approve(Request $request, $id)
    {
        $dsaUser = Auth::user();
        // Eager load type, its workflows, student and student's department
        $leave = Leave::with('type.leaveWorkflows', 'student.department')->findOrFail($id);

        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        $approvalRemarks = $request->input('approval_remarks', null);

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $approvalRemarks,
                'action_at' => now(),
            ]);

            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->first();

            $message = '';
            $sendToStakeholders = true; // Default to true
            $isWeekendLeave = (strtolower($leave->type->name) === 'weekend leave'); // Check if it's weekend leave

            if ($nextWorkflowStep) {
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;

                if ($nextWorkflowStep->action_type === 'approval') {
                    $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . '_approval';
                    $message = 'Leave approved by DSA and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for approval.';
                    $sendToStakeholders = false; // Don't send final stakeholder email yet if forwarded
                } elseif ($nextWorkflowStep->action_type === 'record_keeping') {
                    $leave->overall_status = 'approved'; // Student sees this as approved
                    $message = 'Leave approved by DSA. Sent to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for record keeping.';
                    // For Weekend Leave, stakeholders are skipped
                    if ($isWeekendLeave) {
                        $sendToStakeholders = false;
                    }
                }
            } else {
                // No more steps. DSA is the final decision-maker.
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                $leave->final_remarks = $approvalRemarks;
                $message = 'Leave approved by DSA (Final Approval).';
                // For Weekend Leave, stakeholders are skipped
                if ($isWeekendLeave) {
                    $sendToStakeholders = false;
                }
            }
            $leave->save();
            DB::commit();

            // --- Send Emails (Outside DB Transaction) ---
            try {
                // Email to Student (always, if approved or forwarded)
                if ($leave->overall_status === 'approved' || Str::startsWith($leave->overall_status, 'awaiting_' . strtolower($nextWorkflowStep->approver_role ?? '') . '_record_keeping')) {
                    Mail::to($leave->student->email)->send(new LeaveApprovedFinalToStudent($leave));
                } elseif (Str::startsWith($leave->overall_status, 'awaiting_') && $nextWorkflowStep && $nextWorkflowStep->action_type === 'approval') {
                    Mail::to($leave->student->email)->send(new LeaveForwardedToNextApproverFromDsa($leave, $nextWorkflowStep->approver_role, true));
                }

                // Email to General Stakeholders (conditionally)
                if ($sendToStakeholders && ($leave->overall_status === 'approved' || Str::startsWith($leave->overall_status, 'awaiting_sso_record_keeping'))) {
                    $stakeholderEmails = ['faculty_head@example.com', 'security_office@example.com']; // EXAMPLE
                    // Note: SSO is NOT explicitly added here if they get a dedicated email below for non-weekend leaves
                    foreach ($stakeholderEmails as $email) {
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                           Mail::to($email)->send(new LeaveApprovedToStakeholders($leave, $dsaUser));
                        }
                    }
                }

                // Email to Next Approver (if forwarded for actual approval)
                if ($nextWorkflowStep && $nextWorkflowStep->action_type === 'approval') {
                    $nextApprovers = User::where('role', $nextWorkflowStep->approver_role)->get();
                    foreach($nextApprovers as $nextApprover){
                        if(filter_var($nextApprover->email, FILTER_VALIDATE_EMAIL)) {
                            Mail::to($nextApprover->email)->send(new LeaveForwardedToNextApproverFromDsa($leave, $nextWorkflowStep->approver_role, false));
                        }
                    }
                }

                // Email to Record Keeper (e.g., SSO), always if this step exists, or specifically for Weekend Leave
                if ($nextWorkflowStep && $nextWorkflowStep->action_type === 'record_keeping' && strtolower($nextWorkflowStep->approver_role) === 'sso') {
                    $ssoUsers = User::where('role', 'sso')->get();
                    foreach($ssoUsers as $ssoUser){
                         Mail::to($ssoUser->email)->send(new LeaveApprovedForYourRecords($leave, $dsaUser, 'sso'));
                    }
                } elseif ($isWeekendLeave && $leave->overall_status === 'approved' && (!$nextWorkflowStep || strtolower($nextWorkflowStep->approver_role ?? '') !== 'sso') ) {
                    // This handles case where Weekend Leave might end with DSA and SSO is not explicitly the *next* step for record keeping
                    // but should still be notified as the designated record keeper for approved weekend leaves.
                    $ssoUsers = User::where('role', 'sso')->get();
                     foreach($ssoUsers as $ssoUser){
                         Mail::to($ssoUser->email)->send(new LeaveApprovedForYourRecords($leave, $dsaUser, 'sso'));
                    }
                }


            } catch (\Exception $e) {
                Log::error("DSA Approve - Email sending failed for Leave ID {$id}: " . $e->getMessage());
            }

            return redirect()->route('dsa.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Approve Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while approving the leave: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $dsaUser = Auth::user();
        $leave = Leave::with('student')->findOrFail($id);

        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $rejectionRemarks = $request->input('remarks'); // Get remarks string

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'rejected',
                'remarks' => $request->input('remarks'),
                'action_at' => now(),
            ]);

            $leave->overall_status = 'rejected_by_dsa';
            $leave->final_remarks = $rejectionRemarks;
            $leave->current_approver_role = null;
            $leave->save();

            DB::commit();

            try {
                // === CORRECTED MAILABLE CALL ===
                // Pass the $dsaUser object as the second argument
                // Pass the $rejectionRemarks string as the third argument
                Mail::to($leave->student->email)->send(new LeaveRejectedToStudent($leave, $dsaUser, $rejectionRemarks));
            } catch (\Exception $e) {
                Log::error("DSA Reject - Email sending failed for Leave ID {$id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }

            return redirect()->route('dsa.dashboard')->with('success', 'Leave request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Reject Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while rejecting the leave: ' . $e->getMessage());
        }
    }

    public function approvedRecords()
    {
        $dsaUser = Auth::user();

        // Find leave_ids where this DSA took an 'approved' action
        $approvedLeaveIdsByDsa = LeaveRequestApproval::where('user_id', $dsaUser->id)
            ->where('acted_as_role', 'dsa') // Ensure it was an action taken as DSA
            ->where('action_taken', 'approved')
            ->pluck('leave_id')
            ->unique();

        // Fetch the actual Leave records for these IDs
        // DSA typically sees leaves from all departments they've acted upon.
        $approvedLeaves = Leave::with(['student.department', 'type', 'approvalActions.user'])
            ->whereIn('id', $approvedLeaveIdsByDsa)
            ->orderBy('updated_at', 'desc') // Show most recently actioned ones first
            ->paginate(15);

        return view('dsa.approved-records', compact('approvedLeaves', 'dsaUser'));
    }
    
}
<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval;
// Use User, LeaveType if needed for other logic within these methods
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

// Your Mailable imports
use App\Mail\LeaveApprovedFinalToStudent;
use App\Mail\LeaveApprovedToStakeholders;
use App\Mail\LeaveRejectedToStudent;
use App\Mail\LeaveApprovedForYourRecords;
use App\Mail\LeaveForwardedToNextApproverFromDsa; // If DSA can forward to another approver

class DsaController extends Controller
{
    // ... index() method remains the same ...
    public function index()
    {
        $dsaUser = Auth::user();
        
        // MODIFICATION START: Get user's name and role for the greeting
        $userName = $dsaUser->name;
        $role = $dsaUser->role; // Assumes your User model has a 'role' property
        // MODIFICATION END

        $leaves = Leave::with(['student.department', 'type'])
            ->where('current_approver_role', 'dsa')
            ->where('overall_status', 'awaiting_dsa_approval')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        // MODIFICATION START: Pass the new variables to the view
        return view('dsa.dashboard', compact('leaves', 'dsaUser', 'userName', 'role'));
        // MODIFICATION END
    }

    public function approve(Request $request, $id)
    {
        $dsaUser = Auth::user();
        $leave = Leave::with('type.leaveWorkflows', 'student.department')->findOrFail($id);

        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        $remarksFromForm = $request->input('remarks', null); // <<< Consistently use 'remarks'

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $remarksFromForm, // Use the remarks from the form
                'action_at' => now(),
            ]);

            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->first();
            $message = '';
            $isWeekendLeave = (strtolower($leave->type->name) === 'weekend leave');
            $sendToStakeholders = true; // Default to sending to general stakeholders

            // Prepare Mailable instances
            $studentMail = null;
            $stakeholderMail = null;
            $recordKeeperMail = null;
            $nextApproverMailForStudent = null;
            $nextApproverMailForNext = null;
            $nextApproverEmails = [];


            if ($nextWorkflowStep) {
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;

                if ($nextWorkflowStep->action_type === 'approval') {
                    $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . '_approval';
                    $message = 'Leave approved by DSA and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for approval.';
                    $sendToStakeholders = false; // Don't send final stakeholder email yet

                    $nextApproverMailForStudent = new LeaveForwardedToNextApproverFromDsa($leave, $nextWorkflowStep->approver_role, true);
                    $nextApprovers = User::where('role', $nextWorkflowStep->approver_role)->get();
                    foreach($nextApprovers as $nextApprover){ $nextApproverEmails[] = $nextApprover->email; }
                    if(!empty($nextApproverEmails)){ $nextApproverMailForNext = new LeaveForwardedToNextApproverFromDsa($leave, $nextWorkflowStep->approver_role, false); }

                } elseif ($nextWorkflowStep->action_type === 'record_keeping') {
                    $leave->overall_status = 'approved'; // Student sees this as approved
                    $message = 'Leave approved by DSA. Sent to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for record keeping.';
                    if ($isWeekendLeave) { $sendToStakeholders = false; }

                    $studentMail = new LeaveApprovedFinalToStudent($leave);
                    if ($sendToStakeholders) { // General stakeholders only if not weekend leave
                        $stakeholderMail = new LeaveApprovedToStakeholders($leave, $dsaUser);
                    }
                    // Always send to SSO (as record keeper) for any approved leave reaching this stage
                    $ssoUsers = User::where('role', 'sso')->get(); // Assuming SSO is the role for $nextWorkflowStep
                    if ($ssoUsers->isNotEmpty() && strtolower($nextWorkflowStep->approver_role) === 'sso') {
                        $recordKeeperMail = new LeaveApprovedForYourRecords($leave, $dsaUser, 'sso');
                    }
                }
            } else { // DSA is the final decision-maker
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                $leave->final_remarks = $remarksFromForm;
                $message = 'Leave approved by DSA (Final Approval).';
                if ($isWeekendLeave) { $sendToStakeholders = false; }

                $studentMail = new LeaveApprovedFinalToStudent($leave);
                if ($sendToStakeholders) {
                     $stakeholderMail = new LeaveApprovedToStakeholders($leave, $dsaUser);
                }
                // If it's weekend leave and DSA is final, still ensure SSO gets record keeping email
                if ($isWeekendLeave) {
                    $ssoUsers = User::where('role', 'sso')->get();
                    if ($ssoUsers->isNotEmpty()) {
                        $recordKeeperMail = new LeaveApprovedForYourRecords($leave, $dsaUser, 'sso');
                    }
                }
            }
            $leave->save();
            DB::commit();

            // --- Email Sending Logic ---
            try {
                if ($studentMail) { Mail::to($leave->student->email)->send($studentMail); }
                if ($nextApproverMailForStudent) { Mail::to($leave->student->email)->send($nextApproverMailForStudent); }

                if ($stakeholderMail) { // This already respects $sendToStakeholders flag internally
                    $stakeholderEmails = ['faculty_head@example.com', 'security_office@example.com']; // EXAMPLE
                    // SSO already handled by recordKeeperMail logic or by being in $stakeholderEmails if desired
                    foreach ($stakeholderEmails as $email) {
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)) { Mail::to($email)->send($stakeholderMail); }
                    }
                }
                if ($recordKeeperMail) { // Send to SSO or other record keepers
                    $ssoUsersToNotify = User::where('role', $nextWorkflowStep->approver_role ?? 'sso')->get();
                    foreach($ssoUsersToNotify as $keeper){ Mail::to($keeper->email)->send($recordKeeperMail); }
                }
                if($nextApproverMailForNext && !empty($nextApproverEmails)){
                    foreach($nextApproverEmails as $email){ if(filter_var($email, FILTER_VALIDATE_EMAIL)) { Mail::to($email)->send($nextApproverMailForNext); } }
                }
            } catch (\Exception $e) {
                Log::error("DSA Approve - Email sending failed for Leave ID {$id}: " . $e->getMessage());
            }
            return redirect()->route('dsa.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Approve System Error for Leave ID {$id}: " . $e->getMessage());
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

        $rejectionRemarks = $request->input('remarks', null); // <<< Consistently 'remarks'
        // Validate if remarks are mandatory for rejection
        // $request->validate(['remarks' => 'required|string|max:1000']);

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'rejected',
                'remarks' => $rejectionRemarks,
                'action_at' => now(),
            ]);

            $leave->overall_status = 'rejected_by_dsa';
            $leave->final_remarks = $rejectionRemarks;
            $leave->current_approver_role = null;
            $leave->save();
            DB::commit();

            try {
                Mail::to($leave->student->email)->send(new LeaveRejectedToStudent($leave, $dsaUser, $rejectionRemarks));
            } catch (\Exception $e) {
                Log::error("DSA Reject - Email to student failed: " . $e->getMessage());
            }
            return redirect()->route('dsa.dashboard')->with('success', 'Leave request rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Reject System Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while rejecting the leave.');
        }
    }

    // ... approvedRecords() method (should be present from previous step) ...
    public function approvedRecords(Request $request) // <<< Add Request $request
    {
        $dsaUser = Auth::user();
        // No department check needed for DSA if they see all records they approved

        $searchTerm = $request->input('search');

        // Find leave_ids where this DSA took an 'approved' action
        $approvedLeaveIdsByThisDsa = LeaveRequestApproval::where('user_id', $dsaUser->id)
            ->where('acted_as_role', 'dsa')
            ->where('action_taken', 'approved')
            ->pluck('leave_id')
            ->unique();

        // Base query for fetching approved leaves by this DSA
        $query = Leave::with(['student.department', 'student.program', 'type', 'approvalActions.user'])
            ->whereIn('id', $approvedLeaveIdsByThisDsa);

        if ($searchTerm) {
            $query->where(function ($q_search) use ($searchTerm) {
                $q_search->whereHas('student', function ($studentQuery) use ($searchTerm) {
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
                })
                ->orWhere('reason', 'LIKE', "%{$searchTerm}%");
            });
        }

        $approvedLeaves = $query->orderBy('updated_at', 'desc')
                                ->paginate(15)
                                ->withQueryString(); // Appends search query to pagination links

        return view('dsa.approved-records', compact('approvedLeaves', 'dsaUser'));
    }
}
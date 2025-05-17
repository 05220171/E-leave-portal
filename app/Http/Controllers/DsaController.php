<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval; // <<< ADD THIS
use App\Models\LeaveWorkflow;       // <<< ADD THIS (Though less critical here if DSA is often final approver)
use App\Models\User;                // <<< ADD THIS (To find stakeholders for email)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;       // <<< ADD THIS for transactions
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;    // <<< ADD THIS for sending mail
use Illuminate\Support\Str;
// TODO: Create these Mailable classes
// use App\Mail\LeaveApprovedFinalToStudent;
// use App\Mail\LeaveApprovedToStakeholders;
// use App\Mail\LeaveRejectedByDsaToStudent;
// use App\Mail\LeaveForwardedToNextApproverFromDsa; // If DSA isn't always final

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
        $leave = Leave::with('type.leaveWorkflows', 'student')->findOrFail($id); // Eager load student for email

        // Security Check: Ensure this DSA is the current approver
        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        DB::beginTransaction();
        try {
            // 1. Record DSA's approval action
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $request->input('approval_remarks'), // If you add an approval remarks field
                'action_at' => now(),
            ]);

            // 2. Determine next step OR finalize as approved
            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->first();

            if ($nextWorkflowStep && $nextWorkflowStep->action_type === 'approval') {
                // There IS a next *approval* step (e.g., DAA, President before SSO record keeping)
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;
                $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . '_approval';
                $message = 'Leave approved by DSA and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . '.';
                // TODO: Email student: "Your leave approved by DSA, forwarded to [Next Approver Role]"
                // TODO: Email next approver: "[Student Name]'s leave request awaits your action"

            } elseif ($nextWorkflowStep && $nextWorkflowStep->action_type === 'record_keeping') {
                // Next step is record-keeping (likely SSO). The leave is effectively approved by DSA.
                $leave->overall_status = 'approved'; // Or directly 'approved' and handle SSO viewing differently
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role; // Assign to SSO
                $message = 'Leave approved by DSA. Finalized and sent for record keeping.';
                // Student sees it as approved.
                // TODO: Email student: "Your leave request has been approved!" (from DSA)
                // TODO: Email STAKEHOLDERS (from DSA)
                // TODO: Email next role (SSO): "A leave request has been approved and is ready for your records."

            } else {
                // No more steps OR next step is not an approval type that blocks student view of "approved"
                // DSA is the final decision-making approver in this path.
                $leave->overall_status = 'approved'; // Final approval status
                $leave->current_approver_role = null; // No one else to approve
                $leave->final_remarks = $request->input('approval_remarks'); // DSA's final approval remarks
                $message = 'Leave approved by DSA (Final Approval).';
                // TODO: Email student: "Your leave request has been fully approved!" (from DSA)
                // TODO: Email STAKEHOLDERS (from DSA)
            }
            $leave->save();
            DB::commit();

            // --- SEND EMAILS (Example using placeholder Mailables) ---
            // This is where you'd dispatch your actual Mailable classes
            try {
                // Notify Student
                // Mail::to($leave->student->email)->send(new LeaveApprovedFinalToStudent($leave));

                // Notify Stakeholders -
                // Define $stakeholderEmails array (e.g., from config, database, or hardcoded for now)
                // $stakeholderEmails = ['faculty@example.com', 'security@example.com', 'warden@example.com'];
                // foreach ($stakeholderEmails as $email) {
                //    Mail::to($email)->send(new LeaveApprovedToStakeholders($leave, $dsaUser));
                // }
                Log::info("DSA Approval - Placeholder for sending emails for leave ID: {$leave->id}");

            } catch (\Exception $e) {
                Log::error("DSA Approve - Email sending failed for Leave ID {$id}: " . $e->getMessage());
                // Don't rollback transaction for email failure, but log it.
                // Add a flash message indicating email failure if critical.
            }


            return redirect()->route('dsa.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Approve Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while approving the leave. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $dsaUser = Auth::user();
        $leave = Leave::with('student')->findOrFail($id); // Eager load student

        // Security Check
        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // 1. Record DSA's rejection action
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $dsaUser->id,
                'acted_as_role' => 'dsa',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'rejected',
                'remarks' => $request->input('remarks'),
                'action_at' => now(),
            ]);

            // 2. Update leave status
            $leave->overall_status = 'rejected_by_dsa';
            // Store rejection remarks on the main leave record as the final remarks for this rejection.
            $leave->final_remarks = $request->input('remarks');
            $leave->current_approver_role = null; // Workflow stops
            $leave->save();

            DB::commit();

            // --- SEND EMAIL TO STUDENT ---
            try {
                // Mail::to($leave->student->email)->send(new LeaveRejectedByDsaToStudent($leave, $request->input('remarks')));
                Log::info("DSA Rejection - Placeholder for sending email to student for leave ID: {$leave->id}");
            } catch (\Exception $e) {
                Log::error("DSA Reject - Email sending failed for Leave ID {$id}: " . $e->getMessage());
            }

            return redirect()->route('dsa.dashboard')->with('success', 'Leave request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Reject Error for Leave ID {$id}: " . $e->getMessage());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while rejecting the leave. Please try again.');
        }
    }
}
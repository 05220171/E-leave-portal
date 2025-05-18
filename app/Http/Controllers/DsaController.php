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
            ->orderBy('created_at', 'asc') // Show oldest first
            ->paginate(15);

        return view('dsa.dashboard', compact('leaves', 'dsaUser'));
    }

    public function approve(Request $request, $id)
    {
        $dsaUser = Auth::user();
        $leave = Leave::with(['type.leaveWorkflows', 'student.department'])->findOrFail($id);

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
            $successMessage = '';
            $studentUser = $leave->student;

            // Determine if stakeholders should be notified (not for 'Weekend Leave')
            $notifyStakeholders = true;
            if ($leave->type && strtolower($leave->type->name) === 'weekend leave') {
                $notifyStakeholders = false;
            }

            if ($nextWorkflowStep) {
                // There is a next step in the defined workflow
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;

                if ($nextWorkflowStep->action_type === 'approval') {
                    // Next step is another approval (e.g., DAA, President)
                    $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . '_approval';
                    $successMessage = 'Leave approved by DSA and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for approval.';
                    $leave->save();
                    DB::commit();

                    // Email Student: Approved by DSA, forwarded
                    if ($studentUser && $studentUser->email) {
                        try {
                            Mail::to($studentUser->email)->send(new LeaveApprovedByFirstApproverToStudent($leave, $dsaUser, $nextWorkflowStep->approver_role));
                        } catch (\Exception $e) {
                            Log::error("DSA Approve (Forward) - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                            $successMessage .= ' (Student notification may have failed)';
                        }
                    }
                    // Email Next Approver
                    $nextApprovers = User::where('role', $nextWorkflowStep->approver_role)->get();
                    if ($nextApprovers->isNotEmpty()) {
                        foreach ($nextApprovers as $approver) {
                            if ($approver && $approver->email) {
                                try {
                                    Mail::to($approver->email)->send(new NewLeaveRequestForYourApproval($leave, $approver));
                                } catch (\Exception $e) {
                                     Log::error("DSA Approve (Forward) - Email to next approver {$approver->email} failed for Leave ID {$id}: " . $e->getMessage());
                                }
                            }
                        }
                    } else {
                        Log::warning("DSA Approve (Forward) - No next approver found for role '{$nextWorkflowStep->approver_role}' for leave ID {$leave->id}.");
                    }

                } elseif ($nextWorkflowStep->action_type === 'record_keeping') {
                    // Next step is record keeping (e.g., SSO). Student considers this "approved".
                    $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . '_record_keeping';
                    $successMessage = 'Leave approved by DSA. Sent to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . ' for record keeping.';
                    $leave->save();
                    DB::commit();

                    // Email Student: Approved by DSA (Final from their view)
                    if ($studentUser && $studentUser->email) {
                        try {
                            Mail::to($studentUser->email)->send(new LeaveApprovedFinalToStudent($leave));
                        } catch (\Exception $e) {
                            Log::error("DSA Approve (Record Keeping) - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                             $successMessage .= ' (Student notification may have failed)';
                        }
                    }
                    // Email Stakeholders (conditionally)
                    if ($notifyStakeholders) {
                        $stakeholderEmails = ['faculty_head@jnec.com', 'dean_office@jnec.com', 'sat_office@jnec.com']; // Fetch dynamically
                        foreach ($stakeholderEmails as $emailAddr) {
                            try { Mail::to($emailAddr)->send(new LeaveApprovedToStakeholders($leave, $dsaUser)); }
                            catch (\Exception $e) { Log::error("DSA Approve (Record Keeping) - Email to stakeholder {$emailAddr} failed: " . $e->getMessage());}
                        }
                    }
                    // Email Record Keeper (e.g., SSO)
                    $recordKeepers = User::where('role', $nextWorkflowStep->approver_role)->get();
                    if ($recordKeepers->isNotEmpty()) {
                        foreach ($recordKeepers as $keeper) {
                            Mail::to($keeper->email)->send(new \App\Mail\LeaveApprovedForYourRecords($leave, $dsaUser, $keeper->role));
                            //  if ($keeper && $keeper->email) {
                            //     try { Mail::to($keeper->email)->send(new LeaveApprovedForYourRecords($leave, $keeper)); } // Pass $keeper as the recipient user
                            //     catch (\Exception $e) { Log::error("DSA Approve (Record Keeping) - Email to record keeper {$keeper->email} failed: " . $e->getMessage());}
                            //  }
                        }
                    } else {
                        Log::warning("DSA Approve (Record Keeping) - No record keeper found for role '{$nextWorkflowStep->approver_role}' for leave ID {$leave->id}.");
                    }
                }
            } else {
                // No more steps. DSA is the final decision-maker.
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                $leave->final_remarks = $approvalRemarks;
                $leave->save();
                DB::commit();
                $successMessage = 'Leave approved by DSA (Final Approval).';

                // Email Student: Final Approval
                if ($studentUser && $studentUser->email) {
                    try {
                        Mail::to($studentUser->email)->send(new LeaveApprovedFinalToStudent($leave));
                    } catch (\Exception $e) {
                        Log::error("DSA Approve (Final) - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                        $successMessage .= ' (Student notification may have failed)';
                    }
                }
                // Email Stakeholders (conditionally)
                if ($notifyStakeholders) {
                    $stakeholderEmails = ['faculty_head@jnec.com', 'dean_office@jnec.com', 'sat_office@jnec.com']; // Fetch dynamically
                    foreach ($stakeholderEmails as $emailAddr) {
                        try { Mail::to($emailAddr)->send(new LeaveApprovedToStakeholders($leave, $dsaUser)); }
                        catch (\Exception $e) { Log::error("DSA Approve (Final) - Email to stakeholder {$emailAddr} failed: " . $e->getMessage());}
                    }
                }
            }
            return redirect()->route('dsa.dashboard')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Approve System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while approving the leave. Please try again or contact support.');
        }
    }

    public function reject(Request $request, $id)
    {
        $dsaUser = Auth::user();
        $leave = Leave::with('student.department')->findOrFail($id);

        if ($leave->current_approver_role !== 'dsa' || $leave->overall_status !== 'awaiting_dsa_approval') {
            return redirect()->route('dsa.dashboard')->with('error', 'This leave request is not currently assigned to you.');
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);
        $rejectionRemarks = $request->input('remarks');

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

            $message = 'Leave request rejected successfully.';
            $studentUser = $leave->student;

            if ($studentUser && $studentUser->email) {
                try {
                    // Pass the DSA user who rejected, and the remarks
                    Mail::to($studentUser->email)->send(new LeaveRejectedToStudent($leave, $dsaUser, $rejectionRemarks));
                } catch (\Exception $e) {
                    Log::error("DSA Reject - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                    $message = 'Leave request rejected. (Student email notification may have failed. Please check logs.)';
                }
            }
            return redirect()->route('dsa.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DSA Reject System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('dsa.dashboard')->with('error', 'An error occurred while rejecting the leave. Please try again or contact support.');
        }
    }
}
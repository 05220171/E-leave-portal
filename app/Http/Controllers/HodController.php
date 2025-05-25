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
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveApprovedByFirstApproverToStudent; // <<< ADD THIS
use App\Mail\NewLeaveRequestForYourApproval;
use App\Mail\LeaveRejectedToStudent;
use App\Mail\LeaveApprovedFinalToStudent;

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
            ->orderBy('created_at', 'asc') // Show oldest first
            ->paginate(15);

        return view('hod.dashboard', compact('leaves', 'hodUser'));
    }

    public function approve(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::with(['type.leaveWorkflows', 'student.department'])->findOrFail($id);

        if ($leave->current_approver_role !== 'hod' ||
            $leave->overall_status !== 'awaiting_hod_approval' ||
            !$hodUser->department_id ||
            $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        $approvalRemarks = $request->input('approval_remarks', null);

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
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
            $studentUser = $leave->student; // Get student once

            if ($nextWorkflowStep) {
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;
                $statusSuffix = ($nextWorkflowStep->action_type === 'record_keeping') ? '_record_keeping' : '_approval';
                $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . $statusSuffix;
                $leave->save();
                DB::commit();

                $successMessage = 'Leave approved by HOD and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . '.';

                // --- Send Emails (No Threading) ---
                // 1. To Student: Approved by HOD, forwarded to Next Role
                if ($studentUser && $studentUser->email) {
                    try {
                        // Pass acting approver (HOD) and the role of the next approver
                        Mail::to($studentUser->email)->send(new LeaveApprovedByFirstApproverToStudent($leave, $hodUser, $nextWorkflowStep->approver_role));
                    } catch (\Exception $e) {
                        Log::error("HOD Approve - Email to student (forwarded) failed for Leave ID {$id}: " . $e->getMessage());
                        $successMessage .= ' (Student notification may have failed)';
                    }
                }

                // 2. To Next Approver
                $nextApprovers = User::where('role', $nextWorkflowStep->approver_role)->get();
                if ($nextApprovers->isNotEmpty()) {
                    foreach ($nextApprovers as $approver) {
                        if ($approver && $approver->email) {
                            try {
                                // NewLeaveRequestForYourApproval constructor expects: Leave $leave, User $approver
                                Mail::to($approver->email)->send(new NewLeaveRequestForYourApproval($leave, $approver));
                            } catch (\Exception $e) {
                                Log::error("HOD Approve - Email to next approver {$approver->email} failed for Leave ID {$id}: " . $e->getMessage());
                                $successMessage .= ' (Next approver notification may have failed for ' . $approver->email . ')';
                            }
                        }
                    }
                } else {
                    Log::warning("HOD Approve - No next approver found for role '{$nextWorkflowStep->approver_role}' for leave ID {$leave->id}.");
                    $successMessage .= ' (Could not find next approver to notify)';
                }

            } else {
                // HOD is the final approver in this specific workflow path
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                $leave->final_remarks = $approvalRemarks;
                $leave->save();
                DB::commit();
                $successMessage = 'Leave approved by HOD (Final Approval).';

                // --- Send Final Approval Email to Student ---
                if ($studentUser && $studentUser->email) {
                    try {
                        // LeaveApprovedFinalToStudent constructor expects: Leave $leave
                        Mail::to($studentUser->email)->send(new LeaveApprovedFinalToStudent($leave));
                    } catch (\Exception $e) {
                        Log::error("HOD Approve (Final) - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                        $successMessage .= ' (Student final approval notification may have failed)';
                    }
                }
                // TODO: Potentially notify stakeholders if HOD is final approver.
            }
            return redirect()->route('hod.dashboard')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Approve System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while approving the leave. Please try again or contact support.');
        }
    }

    public function reject(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::with('student.department')->findOrFail($id);

        if ($leave->current_approver_role !== 'hod' ||
            $leave->overall_status !== 'awaiting_hod_approval' ||
            !$hodUser->department_id ||
            $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);
        $rejectionRemarks = $request->input('remarks');

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'rejected',
                'remarks' => $rejectionRemarks,
                'action_at' => now(),
            ]);

            $leave->overall_status = 'rejected_by_hod';
            $leave->final_remarks = $rejectionRemarks;
            $leave->current_approver_role = null;
            $leave->save();
            DB::commit();

            $message = 'Leave request rejected successfully.';
            $studentUser = $leave->student;

            // --- SEND REJECTION EMAIL TO STUDENT (No Threading) ---
            if ($studentUser && $studentUser->email) {
                try {
                    // LeaveRejectedToStudent constructor expects: Leave $leave, User $rejectingUser, ?string $rejectionRemarks
                    Mail::to($studentUser->email)->send(new LeaveRejectedToStudent($leave, $hodUser, $rejectionRemarks));
                } catch (\Exception $e) {
                    Log::error("HOD Reject - Email to student failed for Leave ID {$id}: " . $e->getMessage());
                    $message = 'Leave request rejected. (Student email notification may have failed. Please check logs.)';
                }
            }
            return redirect()->route('hod.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Reject System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while rejecting the leave. Please try again or contact support.');
        }
    }

    public function studentHistory()
    {
        $hod = Auth::user();
        if (!$hod || !$hod->department_id) {
            return redirect()->route('hod.dashboard')
                             ->with('error', 'Unable to determine your department to view history.');
        }
        $departmentId = $hod->department_id;
        $leavesHistory = Leave::with(['student', 'type', 'approvalActions.user'])
            ->whereHas('student', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('hod.student-history', compact('leavesHistory'));
    }

    public function approvedRecords()
    {
        $hodUser = Auth::user();
        if (!$hodUser || !$hodUser->department_id) {
            return redirect()->route('hod.dashboard')
                             ->with('error', 'Unable to determine your department.');
        }

        // Find leave_ids where this HOD took an 'approved' action
        $approvedLeaveIdsByHod = LeaveRequestApproval::where('user_id', $hodUser->id)
            ->where('acted_as_role', 'hod') // Ensure it was an action taken as HOD
            ->where('action_taken', 'approved')
            ->pluck('leave_id') // Get an array of leave IDs
            ->unique(); // Ensure unique IDs if HOD could somehow approve twice (should not happen)

        // Fetch the actual Leave records for these IDs, along with necessary details
        // Only fetch leaves that are from the HOD's department for an extra layer of security/relevance
        $approvedLeaves = Leave::with(['student.department', 'type', 'approvalActions.user'])
            ->whereIn('id', $approvedLeaveIdsByHod)
            ->whereHas('student', function ($query) use ($hodUser) {
                $query->where('department_id', $hodUser->department_id);
            })
            ->orderBy('updated_at', 'desc') // Show most recently actioned ones first
            ->paginate(15); // Or your preferred pagination number

        return view('hod.approved-records', compact('approvedLeaves', 'hodUser'));
    }
}
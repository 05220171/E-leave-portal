<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

// Your Mailable imports
use App\Mail\LeaveApprovedByFirstApproverToStudent;
use App\Mail\NewLeaveRequestForYourApproval;
use App\Mail\LeaveRejectedToStudent;
use App\Mail\LeaveApprovedFinalToStudent;


class HodController extends Controller
{
    // ... index() method ...
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
            ->orderBy('created_at', 'asc')
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

        $remarksFromForm = $request->input('remarks', null); // <<< Now consistently 'remarks'

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $remarksFromForm, // Use the remarks from the form
                'action_at' => now(),
            ]);

            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->first();
            $successMessage = '';
            $studentUser = $leave->student;

            if ($nextWorkflowStep) {
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;
                $statusSuffix = ($nextWorkflowStep->action_type === 'record_keeping') ? '_record_keeping' : '_approval';
                $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . $statusSuffix;
                // Store HOD's remarks on the main leave record if it's being forwarded and remarks exist
                if ($remarksFromForm) {
                    $leave->remarks = "HOD (Approved): " . $remarksFromForm . ($leave->remarks ? "\n---\n" . $leave->remarks : '');
                }
                $successMessage = 'Leave approved by HOD and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . '.';

                // --- Email Logic ---
                if ($studentUser && $studentUser->email) {
                    try { Mail::to($studentUser->email)->send(new LeaveApprovedByFirstApproverToStudent($leave, $hodUser, $nextWorkflowStep->approver_role));}
                    catch (\Exception $e) { Log::error("HOD Approve - Email to student (forwarded) failed: " . $e->getMessage());}
                }
                $nextApprovers = User::where('role', $nextWorkflowStep->approver_role)->get();
                if ($nextApprovers->isNotEmpty()) {
                    foreach ($nextApprovers as $approver) {
                        if ($approver && $approver->email) {
                            try { Mail::to($approver->email)->send(new NewLeaveRequestForYourApproval($leave, $approver)); }
                            catch (\Exception $e) { Log::error("HOD Approve - Email to next approver failed: " . $e->getMessage());}
                        }
                    }
                }

            } else { // HOD is the final approver
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                $leave->final_remarks = $remarksFromForm; // Save as final remarks
                $successMessage = 'Leave approved by HOD (Final Approval).';
                if ($studentUser && $studentUser->email) {
                    try { Mail::to($studentUser->email)->send(new LeaveApprovedFinalToStudent($leave)); }
                    catch (\Exception $e) { Log::error("HOD Approve (Final) - Email to student failed: " . $e->getMessage());}
                }
            }
            $leave->save();
            DB::commit();
            return redirect()->route('hod.dashboard')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Approve System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while approving the leave.');
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

        $rejectionRemarks = $request->input('remarks', null); // <<< Consistently 'remarks'
        // Add validation if remarks are mandatory for rejection
        // $request->validate(['remarks' => 'required|string|max:1000']);


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
            if ($studentUser && $studentUser->email) {
                try { Mail::to($studentUser->email)->send(new LeaveRejectedToStudent($leave, $hodUser, $rejectionRemarks)); }
                catch (\Exception $e) { Log::error("HOD Reject - Email to student failed: " . $e->getMessage()); }
            }
            return redirect()->route('hod.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Reject System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while rejecting the leave.');
        }
    }

    // ... studentHistory() and approvedRecords() methods ...
    public function studentHistory()
    {
        // Your existing studentHistory code
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
        // Your existing approvedRecords code
        $hodUser = Auth::user();
        if (!$hodUser || !$hodUser->department_id) {
            return redirect()->route('hod.dashboard')
                             ->with('error', 'Unable to determine your department.');
        }
        $approvedLeaveIdsByHod = LeaveRequestApproval::where('user_id', $hodUser->id)
            ->where('acted_as_role', 'hod')
            ->where('action_taken', 'approved')
            ->pluck('leave_id')->unique();
        $approvedLeaves = Leave::with(['student.department', 'type', 'approvalActions.user'])
            ->whereIn('id', $approvedLeaveIdsByHod)
            ->whereHas('student', function ($query) use ($hodUser) {
                $query->where('department_id', $hodUser->department_id);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        return view('hod.approved-records', compact('approvedLeaves', 'hodUser'));
    }
}
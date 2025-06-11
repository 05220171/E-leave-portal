<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveRequestApproval;
use App\Models\User;
use App\Models\Department; // Make sure Department model is imported if type-hinting or using its static methods
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
    public function index() // This is your HOD dashboard method
    {
        $hodUser = Auth::user();

        if (!$hodUser || $hodUser->role !== 'hod' || !$hodUser->department_id) { // Added role check for clarity
            // It's good to also log this situation
            Log::warning("Attempt to access HOD dashboard by non-HOD or HOD without department: User ID " . ($hodUser ? $hodUser->id : 'Guest'));
            return redirect()->route('home') // Redirect to a generic home or login if not properly configured
                     ->with('error', 'You are not properly configured as an HOD or your department is not set.');
        }

        $departmentId = $hodUser->department_id;
        $departmentName = $hodUser->department ? $hodUser->department->name : 'Your Department'; // Get department name
        $userName = $hodUser->name; // Get HOD's name

        $leaves = Leave::with(['student.department', 'type'])
            ->where('current_approver_role', 'hod')
            ->where('overall_status', 'awaiting_hod_approval')
            ->whereHas('student', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('hod.dashboard', compact('leaves', 'userName', 'departmentName')); // Pass userName and departmentName
    }

    // ... approve(), reject(), studentHistory(), approvedRecords() methods remain the same ...
    // Ensure they also have access to $hodUser if needed, which they do via Auth::user()

    public function approve(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::with(['type.leaveWorkflows', 'student.department'])->findOrFail($id);

        // Added role check to ensure only HODs from the correct department can approve
        if ($hodUser->role !== 'hod' ||
            $leave->current_approver_role !== 'hod' ||
            $leave->overall_status !== 'awaiting_hod_approval' ||
            !$hodUser->department_id ||
            $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        $remarksFromForm = $request->input('remarks', null);

        DB::beginTransaction();
        try {
            LeaveRequestApproval::create([
                'leave_id' => $leave->id,
                'user_id' => $hodUser->id,
                'acted_as_role' => 'hod',
                'workflow_step_number' => $leave->current_step_number,
                'action_taken' => 'approved',
                'remarks' => $remarksFromForm,
                'action_at' => now(),
            ]);

            $currentStepNumber = $leave->current_step_number;
            $nextWorkflowStep = $leave->type->leaveWorkflows
                                      ->where('step_number', '>', $currentStepNumber)
                                      ->sortBy('step_number') // Ensure it gets the very next step
                                      ->first();
            $successMessage = '';
            $studentUser = $leave->student;

            if ($nextWorkflowStep) {
                $leave->current_step_number = $nextWorkflowStep->step_number;
                $leave->current_approver_role = $nextWorkflowStep->approver_role;
                $statusSuffix = ($nextWorkflowStep->action_type === 'record_keeping') ? '_record_keeping' : '_approval';
                $leave->overall_status = 'awaiting_' . strtolower($nextWorkflowStep->approver_role) . $statusSuffix;
                if ($remarksFromForm) {
                    $leave->remarks = "HOD (Approved): " . $remarksFromForm . ($leave->remarks ? "\n---\n" . $leave->remarks : '');
                }
                $successMessage = 'Leave approved by HOD and forwarded to ' . Str::title(str_replace('_', ' ', $nextWorkflowStep->approver_role)) . '.';

                if ($studentUser && $studentUser->email) {
                    try { Mail::to($studentUser->email)->send(new LeaveApprovedByFirstApproverToStudent($leave, $hodUser, $nextWorkflowStep->approver_role));}
                    catch (\Exception $e) { Log::error("HOD Approve - Email to student (forwarded) failed for leave ID {$leave->id}: " . $e->getMessage());}
                }
                // Ensure $nextApprovers considers department for HOD roles if the next approver is also an HOD (unlikely but good to be aware)
                $nextApproversQuery = User::where('role', $nextWorkflowStep->approver_role);
                if(strtolower($nextWorkflowStep->approver_role) === 'hod' && $studentUser->department_id) {
                    // This case is unusual (HOD forwarding to another HOD) but handled if needed
                    $nextApproversQuery->where('department_id', $studentUser->department_id);
                }
                $nextApprovers = $nextApproversQuery->get();

                if ($nextApprovers->isNotEmpty()) {
                    foreach ($nextApprovers as $approver) {
                        if ($approver && $approver->email) {
                            try { Mail::to($approver->email)->send(new NewLeaveRequestForYourApproval($leave, $approver)); }
                            catch (\Exception $e) { Log::error("HOD Approve - Email to next approver {$approver->email} failed for leave ID {$leave->id}: " . $e->getMessage());}
                        }
                    }
                } else {
                     Log::warning("No next approver found for role '{$nextWorkflowStep->approver_role}' for leave ID {$leave->id}.");
                }

            } else {
                $leave->overall_status = 'approved';
                $leave->current_approver_role = null;
                // $leave->final_remarks = $remarksFromForm; // Already handled by LeaveRequestApproval
                if ($remarksFromForm) { // Add to main leave remarks if final and has remarks
                    $leave->remarks = "HOD (Final Approval): " . $remarksFromForm . ($leave->remarks ? "\n---\n" . $leave->remarks : '');
                }
                $successMessage = 'Leave approved by HOD (Final Approval).';
                if ($studentUser && $studentUser->email) {
                    try { Mail::to($studentUser->email)->send(new LeaveApprovedFinalToStudent($leave)); }
                    catch (\Exception $e) { Log::error("HOD Approve (Final) - Email to student failed for leave ID {$leave->id}: " . $e->getMessage());}
                }
            }
            $leave->save();
            DB::commit();
            return redirect()->route('hod.dashboard')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Approve System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while approving the leave. Please check logs.');
        }
    }

    public function reject(Request $request, $id)
    {
        $hodUser = Auth::user();
        $leave = Leave::with('student.department')->findOrFail($id);

        if ($hodUser->role !== 'hod' ||
            $leave->current_approver_role !== 'hod' ||
            $leave->overall_status !== 'awaiting_hod_approval' ||
            !$hodUser->department_id ||
            $leave->student->department_id !== $hodUser->department_id) {
            return redirect()->route('hod.dashboard')->with('error', 'This leave request is not currently assigned to you or is not from your department.');
        }

        $rejectionRemarks = $request->input('remarks', null);
        // if (!$rejectionRemarks) {
        //     return back()->withInput()->with('error', 'Remarks are required for rejecting a leave request.');
        // }


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
            $leave->final_remarks = "HOD (Rejected): " . $rejectionRemarks; // Prepend who rejected
            $leave->current_approver_role = null; // No further approver
            $leave->current_step_number = null; // Clear current step
            $leave->save();
            DB::commit();

            $message = 'Leave request rejected successfully.';
            $studentUser = $leave->student;
            if ($studentUser && $studentUser->email) {
                try { Mail::to($studentUser->email)->send(new LeaveRejectedToStudent($leave, $hodUser, $rejectionRemarks)); }
                catch (\Exception $e) { Log::error("HOD Reject - Email to student failed for leave ID {$leave->id}: " . $e->getMessage()); }
            }
            return redirect()->route('hod.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("HOD Reject System Error for Leave ID {$id}: " . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->route('hod.dashboard')->with('error', 'An error occurred while rejecting the leave. Please check logs.');
        }
    }


    public function studentHistory()
    {
        $hod = Auth::user();
        if (!$hod || $hod->role !== 'hod' || !$hod->department_id) {
            return redirect()->route('home') // Or appropriate redirect
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
        if (!$hodUser || $hodUser->role !== 'hod' || !$hodUser->department_id) {
            return redirect()->route('home') // Or appropriate redirect
                             ->with('error', 'Unable to determine your department.');
        }
        // Fetch leaves that this HOD has an approval record for, from their department
        $approvedLeaves = Leave::with(['student.department', 'type', 'approvalActions.user'])
            ->whereHas('approvalActions', function($query) use ($hodUser) {
                $query->where('user_id', $hodUser->id)
                      ->where('acted_as_role', 'hod')
                      ->where('action_taken', 'approved');
            })
            ->whereHas('student', function ($query) use ($hodUser) {
                $query->where('department_id', $hodUser->department_id);
            })
            ->orderBy('updated_at', 'desc') // Order by when they were last updated (likely approved)
            ->paginate(15);

        return view('hod.approved-records', compact('approvedLeaves', 'hodUser'));
    }
}
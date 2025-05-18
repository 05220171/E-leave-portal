<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Make sure this is used

// Import your simplified Mailables (that don't handle threading themselves)
use App\Mail\LeaveRequestSubmittedToStudent;
use App\Mail\NewLeaveRequestForYourApproval;

class StudentLeaveController extends Controller
{
    public function create(): View
    {
        $activeLeaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        return view('student.apply_leave', compact('activeLeaveTypes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $fromDate = Carbon::parse($validatedData['from_date']);
        $toDate = Carbon::parse($validatedData['to_date']);
        $workingDays = $this->calculateWorkingDays($fromDate, $toDate);

        $documentPath = null;
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            $studentId = Auth::id();
            $fileName = time() . '_' . $request->file('document')->getClientOriginalName();
            $path = $request->file('document')->storeAs("leave_documents/{$studentId}", $fileName, 'public');
            $documentPath = $path;
        }

        $selectedLeaveType = LeaveType::with('leaveWorkflows')->find($validatedData['leave_type_id']);
        if (!$selectedLeaveType || $selectedLeaveType->leaveWorkflows->isEmpty()) {
            return redirect()->back()
                             ->with('error', 'The selected leave type does not have an approval workflow configured. Please contact admin.')
                             ->withInput();
        }
        $firstWorkflowStep = $selectedLeaveType->leaveWorkflows->first();
        $initialStatusPrefix = 'awaiting_';
        $initialStatusSuffix = ($firstWorkflowStep->action_type === 'record_keeping') ? '_record_keeping' : '_approval';
        $initialOverallStatus = $initialStatusPrefix . strtolower($firstWorkflowStep->approver_role) . $initialStatusSuffix;

        $dataToCreate = [
            'student_id' => Auth::id(),
            'leave_type_id' => $validatedData['leave_type_id'],
            'start_date' => $validatedData['from_date'],
            'end_date'   => $validatedData['to_date'],
            'reason'     => $validatedData['reason'],
            'number_of_days' => $workingDays,
            'document'   => $documentPath,
            'current_step_number' => $firstWorkflowStep->step_number,
            'current_approver_role' => $firstWorkflowStep->approver_role,
            'overall_status' => $initialOverallStatus,
            // NO 'email_thread_id' or 'initial_message_id_header' needed here
        ];

        $leave = Leave::create($dataToCreate);
        // $studentMailable = null; // Not needed if we don't access properties from it after sending

        if ($leave) {
            $studentUser = $leave->student; // Get the student user object associated with the leave

            // Send Confirmation Email to Student
            if ($studentUser && $studentUser->email) {
                try {
                    Mail::to($studentUser->email)->send(new LeaveRequestSubmittedToStudent($leave));
                } catch (\Exception $e) {
                    Log::error("Failed to send leave submission confirmation email to student {$studentUser->email} for leave ID {$leave->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("Student or student email not found for leave ID {$leave->id} during submission email process.");
            }


            // Send Notification to First Approver
            $firstApproverRole = $firstWorkflowStep->approver_role;
            $firstApprovers = collect();

            if (strtolower($firstApproverRole) === 'hod') {
                $studentDepartmentId = $studentUser->department_id ?? null; // Ensure student has department
                if ($studentDepartmentId) {
                    $firstApprovers = User::where('role', 'hod')
                                          ->where('department_id', $studentDepartmentId)
                                          ->get();
                }
            } else {
                $firstApprovers = User::where('role', $firstApproverRole)->get();
            }

            if ($firstApprovers->isNotEmpty()) {
                foreach ($firstApprovers as $approver) {
                    if ($approver && $approver->email) {
                        try {
                            // The NewLeaveRequestForYourApproval mailable now only needs $leave and $approver
                            Mail::to($approver->email)->send(new NewLeaveRequestForYourApproval($leave, $approver));
                        } catch (\Exception $e) {
                            Log::error("Failed to send new leave notification to {$firstApproverRole} {$approver->email} for leave ID {$leave->id}: " . $e->getMessage());
                        }
                    } else {
                         Log::warning("Approver object or email missing for role '{$firstApproverRole}' for leave ID: {$leave->id}.");
                    }
                }
            } else {
                Log::warning("No first approver found for role '{$firstApproverRole}' for leave ID {$leave->id}. Student department ID: " . ($studentUser->department_id ?? 'N/A'));
            }
        }

        $approverRoleTitle = Str::title(str_replace('_', ' ', $firstWorkflowStep->approver_role));
        return redirect()->route('student.leave-history')
                         ->with('success', "Leave applied successfully! Duration: $workingDays day(s). Awaiting approval from {$approverRoleTitle}. You will receive a confirmation email.");
    }

    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $currentDate = $start->copy();
        while ($currentDate <= $end) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        return $workingDays;
    }

    public function history(): View
    {
         $leaves = Leave::with('type')
                        ->where('student_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
         return view('student.leave-history', compact('leaves'));
    }

    public function status(): View
    {
        $finalizedStatuses = [
            'approved',
            'cancelled',
        ];
        $activeLeaves = Leave::with('type')
                             ->where('student_id', Auth::id())
                             ->where(function ($query) use ($finalizedStatuses) {
                                 $query->whereNotIn('overall_status', $finalizedStatuses)
                                       ->whereRaw("NOT overall_status LIKE 'rejected_by_%'");
                             })
                             ->orderBy('created_at', 'desc')
                             ->paginate(10);
        return view('student.leave-status', compact('activeLeaves'));
    }

    public function cancel($id)
    {
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();
        if (Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled') {
             $leave->update([
                'overall_status' => 'cancelled',
                'current_approver_role' => null,
                'current_step_number' => null,
             ]);
             // TODO: Notify relevant current approver that this was cancelled
             return back()->with('success', 'Leave request has been cancelled.');
        } else {
            return back()->with('error', 'This leave request cannot be cancelled at its current stage (' . Str::title(str_replace('_', ' ', $leave->overall_status)) . ').');
        }
    }

    public function delete($id)
    {
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();
        if ($leave->overall_status === 'cancelled') {
            if ($leave->document && Storage::disk('public')->exists($leave->document)) {
                Storage::disk('public')->delete($leave->document);
            }
            $leave->delete();
            return back()->with('success', 'Leave record deleted from history.');
        } else {
            return back()->with('error', 'This leave record cannot be deleted at its current stage.');
        }
    }
}
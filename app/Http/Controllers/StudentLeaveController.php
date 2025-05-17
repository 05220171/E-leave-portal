<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType; // To fetch dynamic leave types
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // For document handling (if you implement uploads)
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Str; // For Str::title

class StudentLeaveController extends Controller
{
    /**
     * Show the form for creating a new leave application.
     */
    public function create(): View
    {
        // Fetch active leave types from the database to populate the dropdown
        $activeLeaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        return view('student.apply_leave', compact('activeLeaveTypes'));
    }

    /**
     * Store a newly created leave application in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id', // Validate against the ID of an existing, active LeaveType
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048', // Max 2MB
        ]);

        // Calculate working days
        $fromDate = Carbon::parse($validatedData['from_date']);
        $toDate = Carbon::parse($validatedData['to_date']);
        $workingDays = $this->calculateWorkingDays($fromDate, $toDate);

        // Handle document upload
        $documentPath = null;
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            $studentId = Auth::id();
            // Store in 'public/leave_documents/student_id/timestamp_filename.ext'
            $fileName = time() . '_' . $request->file('document')->getClientOriginalName();
            $path = $request->file('document')->storeAs("leave_documents/{$studentId}", $fileName, 'public');
            $documentPath = $path; // This path is relative to 'storage/app/public/'
        }

        // --- Initialize Workflow based on selected LeaveType ---
        $selectedLeaveType = LeaveType::with('leaveWorkflows')->find($validatedData['leave_type_id']);

        if (!$selectedLeaveType) { // Should not happen due to 'exists' validation, but good check
            return redirect()->back()
                             ->with('error', 'Invalid leave type selected.')
                             ->withInput();
        }
        if ($selectedLeaveType->leaveWorkflows->isEmpty()) {
            return redirect()->back()
                             ->with('error', 'The selected leave type ("' . $selectedLeaveType->name . '") does not have an approval workflow defined. Please contact an administrator.')
                             ->withInput();
        }

        // The leaveWorkflows relationship in LeaveType model is already ordered by step_number
        $firstWorkflowStep = $selectedLeaveType->leaveWorkflows->first();

        // Determine the initial overall_status string based on the first step's role and action type
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
            // 'remarks' and 'final_remarks' are typically not set by the student on initial submission
        ];

        $leave = Leave::create($dataToCreate);

        // TODO: Implement Email Notification to the first approver
        // 1. Get the user(s) based on $firstWorkflowStep->approver_role (and student's department if HOD).
        // 2. Send them an email. (e.g., Mail::to($approverEmails)->send(new NewLeaveRequestAlert($leave));)

        $approverRoleTitle = Str::title(str_replace('_', ' ', $firstWorkflowStep->approver_role));
        return redirect()->route('student.leave-history') // Or student.leave-status if preferred
                         ->with('success', "Leave applied successfully for $workingDays working day(s). Your request is now awaiting action from: {$approverRoleTitle}.");
    }

    /**
     * Calculates working days (Monday-Friday) between two dates, inclusive.
     */
    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $currentDate = $start->copy();
        while ($currentDate <= $end) {
            if (!$currentDate->isWeekend()) { // isWeekend() checks for Saturday or Sunday
                $workingDays++;
            }
            $currentDate->addDay();
        }
        return $workingDays;
    }

    /**
     * Display the leave history for the student.
     */
     public function history(): View
     {
         $leaves = Leave::with('type') // Eager load the LeaveType model using the 'type' relationship
                        ->where('student_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10); // Adjust pagination as needed

         return view('student.leave-history', compact('leaves'));
     }

    /**
     * Display the status of active/pending leave applications for the student.
     */
    public function status(): View
    {
        // Define statuses that mean the leave request is no longer "in progress" from student's POV
        $finalizedStatuses = [
            'approved',
            'cancelled', // Covers 'cancelled_by_student' and 'cancelled_by_admin'
            // Add all possible rejection statuses; a more robust way is to check for 'rejected_by_' prefix
        ];

        $activeLeaves = Leave::with('type') // Eager load leave type
                             ->where('student_id', Auth::id())
                             ->where(function ($query) use ($finalizedStatuses) {
                                 $query->whereNotIn('overall_status', $finalizedStatuses)
                                       ->whereRaw("NOT overall_status LIKE 'rejected_by_%'"); // Exclude all rejection types
                             })
                             ->orderBy('created_at', 'desc')
                             ->paginate(10);

        return view('student.leave-status', compact('activeLeaves'));
    }

    /**
     * Cancel a leave application.
     */
    public function cancel($id)
    {
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();

        // Student can cancel if the status starts with 'awaiting_' (e.g., 'awaiting_hod_approval')
        // and it's not already cancelled.
        if (Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled') {
             $leave->update([
                'overall_status' => 'cancelled', // Or 'cancelled_by_student' for more detail
                'current_approver_role' => null,   // No longer pending anyone
                'current_step_number' => null,       // Reset workflow step
             ]);

            // TODO: Notify the current_approver_role (stored before update) that the request has been cancelled.

             return back()->with('success', 'Leave request has been cancelled.');
        } else {
            return back()->with('error', 'This leave request cannot be cancelled at its current stage (' . Str::title(str_replace('_', ' ', $leave->overall_status)) . ').');
        }
    }

    /**
     * Delete a leave history record.
     * Students should typically only delete requests they cancelled.
     */
    public function delete($id)
    {
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();

        // Only allow deletion of leaves that the student themselves cancelled.
        // Or perhaps very old, fully processed (rejected/approved long ago) leaves - policy decision.
        if ($leave->overall_status === 'cancelled') { // Or 'cancelled_by_student'
            // Optional: Delete associated document from storage before deleting the record
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
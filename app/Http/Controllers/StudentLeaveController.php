<?php

namespace App\Http\Controllers;

use App\Models\Leave; // Ensure Leave model is imported
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\LeaveRequestSubmittedToStudent;
use App\Mail\NewLeaveRequestForYourApproval;

class StudentLeaveController extends Controller
{
    const WEEKEND_LEAVE_TYPE_NAME = 'Weekend leave';

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

        $selectedLeaveType = LeaveType::with('leaveWorkflows')->find($validatedData['leave_type_id']);
        if (!$selectedLeaveType) {
            return redirect()->back()->with('error', 'Invalid leave type selected.')->withInput();
        }

        $isWeekendLeave = (strtolower($selectedLeaveType->name) === strtolower(self::WEEKEND_LEAVE_TYPE_NAME));

        if ($isWeekendLeave) {
            $request->validate([
                'from_date' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                    function ($attribute, $value, $fail) {
                        $date = Carbon::parse($value);
                        if (!$date->isWeekend()) {
                            $fail('For Weekend Leave, the ' . str_replace('_', ' ', $attribute) . ' must be a Saturday or Sunday.');
                        }
                    },
                ],
                'to_date' => [
                    'required',
                    'date',
                    'after_or_equal:from_date',
                    function ($attribute, $value, $fail) {
                        $date = Carbon::parse($value);
                        if (!$date->isWeekend()) {
                            $fail('For Weekend Leave, the ' . str_replace('_', ' ', $attribute) . ' must be a Saturday or Sunday.');
                        }
                    },
                ],
            ]);
        }

        $fromDate = Carbon::parse($validatedData['from_date']);
        $toDate = Carbon::parse($validatedData['to_date']);
        $calculatedDays = $this->calculateDays($fromDate, $toDate, $selectedLeaveType);

        if ($calculatedDays <= 0 && !$isWeekendLeave) {
             return redirect()->back()
                             ->with('error', 'The selected date range does not result in any valid leave days for the chosen leave type.')
                             ->withInput();
        }
         if ($isWeekendLeave && $calculatedDays <= 0 && $fromDate->notEqualTo($toDate)) {
            return redirect()->back()
                             ->with('error', 'The selected date range for Weekend Leave does not include any Saturdays or Sundays.')
                             ->withInput();
        }

        $documentPath = null;
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            $studentId = Auth::id();
            $fileName = time() . '_' . $request->file('document')->getClientOriginalName();
            $path = $request->file('document')->storeAs("leave_documents/{$studentId}", $fileName, 'public');
            $documentPath = $path;
        }

        if ($selectedLeaveType->leaveWorkflows->isEmpty()) {
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
            'number_of_days' => $calculatedDays,
            'document'   => $documentPath,
            'current_step_number' => $firstWorkflowStep->step_number,
            'current_approver_role' => $firstWorkflowStep->approver_role,
            'overall_status' => $initialOverallStatus,
        ];

        $leave = Leave::create($dataToCreate);

        if ($leave) {
            $studentUser = $leave->student;
            if ($studentUser && $studentUser->email) {
                try {
                    Mail::to($studentUser->email)->send(new LeaveRequestSubmittedToStudent($leave));
                } catch (\Exception $e) {
                    Log::error("Failed to send leave submission confirmation email to student {$studentUser->email} for leave ID {$leave->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("Student or student email not found for leave ID {$leave->id} during submission email process.");
            }

            $firstApproverRole = $firstWorkflowStep->approver_role;
            $firstApprovers = collect();
            if (strtolower($firstApproverRole) === 'hod') {
                $studentDepartmentId = $studentUser->department_id ?? null;
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
        $dayOrDays = $calculatedDays == 1 ? 'day' : 'days';
        return redirect()->route('student.leave-history')
                         ->with('success', "Leave applied successfully! Duration: $calculatedDays $dayOrDays. Awaiting approval from {$approverRoleTitle}. You will receive a confirmation email.");
    }

    private function calculateDays(Carbon $start, Carbon $end, LeaveType $leaveType): int
    {
        $days = 0;
        $currentDate = $start->copy();
        $isWeekendLeaveType = (strtolower($leaveType->name) === strtolower(self::WEEKEND_LEAVE_TYPE_NAME));

        while ($currentDate <= $end) {
            if ($isWeekendLeaveType) {
                if ($currentDate->isWeekend()) {
                    $days++;
                }
            } else {
                if (!$currentDate->isWeekend()) {
                    $days++;
                }
            }
            $currentDate->addDay();
        }
        return $days;
    }

    public function status(): View
    {
        $studentId = Auth::id();
        $approvedLeaves = Leave::with(['type', 'student.department'])
                        ->where('student_id', $studentId)
                        ->where('overall_status', 'approved')
                        ->orderBy('start_date', 'desc')
                        ->paginate(10);
        return view('student.leave-status', compact('approvedLeaves'));
    }

    public function downloadLeaveCertificate(Leave $leave): \Illuminate\Http\Response
    {
        // This method still uses Route Model Binding as per your routes file:
        // Route::get('/leave-certificate/{leave}/download', ...)
        if ($leave->student_id !== Auth::id()) {
            abort(403, 'Unauthorized action. You can only download your own leave records.');
        }
        if ($leave->overall_status !== 'approved') {
            return redirect()->route('student.status')
                             ->with('error', 'A leave certificate can only be downloaded for fully approved leave requests.');
        }
        $leave->loadMissing(['student.department', 'type', 'approvalActions.user']);
        $pdfData = ['leave' => $leave];
        $studentNameSanitized = Str::slug($leave->student->name ?? 'student', '_');
        $leaveStartDateSanitized = $leave->start_date->format('Ymd');
        $filename = "leave_record_{$studentNameSanitized}_{$leave->id}_{$leaveStartDateSanitized}.pdf";
        try {
            $pdf = Pdf::loadView('pdf.leave_certificate', $pdfData);
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error("PDF Generation Error for Leave ID {$leave->id}: " . $e->getMessage());
            return redirect()->route('student.status')
                             ->with('error', 'Could not generate the leave certificate due to an error. Please try again later.');
        }
    }

    public function history(): View
    {
         $leaves = Leave::with(['type', 'student.department'])
                        ->where('student_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
         return view('student.leave-history', compact('leaves'));
    }


    /**
     * Cancel a leave request.
     * Route parameter is {id}, so we manually fetch the Leave model.
     */
    public function cancel(Request $request, $id)
    {
        $leave = Leave::find($id);

        if (!$leave) {
            // If the leave record with $id doesn't exist at all
            abort(404, 'Leave record not found.');
        }

        if ($leave->student_id !== Auth::id()) {
            // Check if the authenticated user owns this leave record
            abort(403, 'You are not authorized to cancel this specific leave request.');
        }

        // Check if the leave status allows cancellation
        if (Str::startsWith($leave->overall_status, 'awaiting_') && $leave->overall_status !== 'cancelled') {
             $leave->update([
                'overall_status' => 'cancelled',
                'current_approver_role' => null,
                'current_step_number' => null,
             ]);
             // TODO: Consider notifying relevant current approver that this was cancelled
             return back()->with('success', 'Leave request has been cancelled.');
        }

        // If status doesn't allow cancellation
        return back()->with('error', 'This leave request cannot be cancelled at its current stage.');
    }

    /**
     * Delete a leave request.
     * Route parameter is {id} (as per Route::delete('/leave/{id}', ...)),
     * so we manually fetch the Leave model.
     */
    public function delete(Request $request, $id)
    {
        $leave = Leave::find($id);

        if (!$leave) {
            abort(404, 'Leave record not found for deletion.');
        }

        if ($leave->student_id !== Auth::id()) {
            abort(403, 'You are not authorized to delete this leave request.');
        }

        if ($leave->overall_status === 'cancelled') {
            // Only allow deletion if the leave is already cancelled
            if ($leave->document && Storage::disk('public')->exists($leave->document)) {
                Storage::disk('public')->delete($leave->document);
            }
            $leave->delete();
            return redirect()->route('student.leave-history')->with('success', 'Cancelled leave record deleted from history.');
        }

        return redirect()->route('student.leave-history')->with('error', 'This leave record cannot be deleted as it is not in a cancelled state.');
    }
}
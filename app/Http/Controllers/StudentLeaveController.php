<?php

namespace App\Http\Controllers;

use App\Models\Leave; // Make sure your Leave model is updated too
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rule; // Import Rule for enum validation
use Carbon\Carbon;             // <<< ADD THIS LINE
use Carbon\CarbonPeriod;       // <<< ADD THIS LINE


class StudentLeaveController extends Controller
{
    /**
     * Show the form for creating a new leave application.
     */
    public function create(): View
    {
        // Ensure this view exists: resources/views/student/apply_leave.blade.php
        return view('student.apply_leave');
    }

    /**
     * Store a newly created leave application in storage.
     */
    public function store(Request $request)
    {
        // Validate based on form input names, map to DB columns later
        // Use the actual enum values from your migration for validation
        $validatedData = $request->validate([
            'leave_type' => [
                'required',
                Rule::in(['emergency', 'regular']), // Validate against allowed enum values
            ],
            // Assuming form names are 'from_date' and 'to_date'
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
            // Add validation for 'document' if needed
            // 'document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // Calculate the number of days
           // Parse the dates
        $fromDate = Carbon::parse($validatedData['from_date']);
        $toDate = Carbon::parse($validatedData['to_date']);

        // Calculate the number of days between from_date and to_date (inclusive)
        $workingDays = $fromDate->diffInDays($toDate) + 1; // +1 to make it inclusive

        // If you need to exclude weekends (Saturday, Sunday), use this
        $workingDays = $this->calculateWorkingDays($fromDate, $toDate);


        // Prepare data for creation, mapping form names to DB column names
        $dataToCreate = [
            // --- Use DB Column Names ---   // --- Get Data from Form Input Names ---
            'student_id' => Auth::id(),        // Correct foreign key
            'leave_type' => $validatedData['leave_type'], // Use validated data
            'start_date' => $validatedData['from_date'], // Map form 'from_date' to DB 'start_date'
            'end_date'   => $validatedData['to_date'],   // Map form 'to_date' to DB 'end_date'
            'reason'     => $validatedData['reason'],    // Use validated data
            'number_of_days' => $workingDays, 
            // 'status' will automatically use the default 'awaiting_hod_approval' set in migration
            // Handle document upload if applicable
            // 'document'   => $path ?? null, // If you uploaded a file and got its path
        ];

        Leave::create($dataToCreate); // Use the prepared data

        // Redirect back to the history page or dashboard with a success message
        // Ensure 'student.leave-history' route exists in routes/web.php
        return redirect()->route('student.leave-history')->with('success', "Leave applied successfully! Duration: $workingDays day(s)");
    }

    private function calculateWorkingDays(Carbon $start, Carbon $end)
    {
        $workingDays = 0;
        while ($start <= $end) {
            // Check if it's a working day (not Saturday or Sunday)
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }
        return $workingDays;
    }

    /**
     * Display the leave history for the student.
     */
     public function history(): View
     {
         // Use the correct foreign key 'student_id'
         $leaves = Leave::where('student_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

         // Ensure this view exists: resources/views/student/leave_history.blade.php
         return view('student.leave-history', compact('leaves'));
     }


    /**
     * Update the specified leave to 'cancelled'.
     * Note: Uses the exact enum value 'cancelled'.
     */
    public function cancel($id)
    {
        // Use the correct foreign key 'student_id'
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();

        // Check against the actual enum values from the migration
        // Allow cancellation only for statuses before final approval/rejection
        $cancellableStatuses = [
            'awaiting_hod_approval',
            'awaiting_dsa_approval',
            'awaiting_sso_approval',
            // Add others if applicable, e.g., maybe 'approved' if policy allows withdrawal?
        ];

        if (in_array($leave->status, $cancellableStatuses)) {
             // Use the exact enum value 'cancelled' from your migration
             $leave->update(['status' => 'cancelled']);
             return back()->with('success', 'Leave request cancelled.');
        } else {
            return back()->with('error', 'This leave request cannot be cancelled at its current stage.');
        }
    }

    /**
     * Remove the specified leave history record from storage.
     */
    public function delete($id)
    {
        // Use the correct foreign key 'student_id'
        $leave = Leave::where('id', $id)->where('student_id', Auth::id())->firstOrFail();

        // Check against the actual enum values from the migration
        // Allow deletion only for terminal states (cancelled or rejected)
        $deletableStatuses = [
            'cancelled',
            'rejected_by_hod',
            'rejected_by_dsa',
            'rejected_by_sso',
        ];

        if (in_array($leave->status, $deletableStatuses)) {
            $leave->delete();
            return back()->with('success', 'Leave history deleted.');
        } else {
            return back()->with('error', 'This leave history cannot be deleted (must be cancelled or rejected).');
        }
    }

    // --- ADDED METHOD ---
    /**
     * Display the status of active/pending leave applications for the student.
     */
    public function status(): View
    {
        // Define the statuses considered "final" or "inactive"
        $inactiveStatuses = [
            'approved',
            'rejected_by_hod',
            'rejected_by_dsa',
            'rejected_by_sso',
            'cancelled'
        ];

        // Query for leaves belonging to the student that are NOT in the inactive list
        $activeLeaves = Leave::where('student_id', Auth::id())
                             ->whereNotIn('status', $inactiveStatuses)
                             ->orderBy('created_at', 'desc') // Show newest first
                             ->paginate(10); // Paginate results

        // Return the view, passing the active leave data
        // Ensure view exists: resources/views/student/leave-status.blade.php
        return view('student.leave-status', compact('activeLeaves'));
    }
    // --- END OF ADDED METHOD ---

} // End of StudentLeaveController class
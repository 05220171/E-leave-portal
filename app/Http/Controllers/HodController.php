<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeaveStatusUpdatedNotification;
use Illuminate\Support\Facades\Log; // For error logging

class HodController extends Controller
{
    // Show all pending leaves for HOD's department
    public function index()
    {
        $hod = Auth::user(); // Logged-in HOD
        $departmentId = $hod->department_id;

        $leaves = Leave::whereHas('student', function ($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })->where('status', 'awaiting_hod_approval')
          ->with('student') // eager load for performance
          ->get();

        // !!! IMPORTANT: Ensure this view name ('hod.dashboard' or 'hod.approve-reject-list')
        // matches the file containing the list/table view based on previous steps !!!
        return view('hod.dashboard', compact('leaves'));
    }

    // Approve leave - forwards to DSA
    public function approve($id)
    {
        $leave = Leave::findOrFail($id);

        // Update leave status to DSA review
        $leave->status = 'awaiting_dsa_approval';
        $leave->save();

        return redirect()->back()->with('success', 'Leave approved and sent to DSA.');
    }

    // Reject leave - notify student
    // Modify the reject method
    public function reject(Request $request, $id) // Ensure Request $request is here
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000', // Add validation for remarks
        ]);

        $leave = Leave::findOrFail($id);
        $rejectionReason = $request->input('remarks'); // Get remarks from the request

        $leave->status = 'rejected_by_hod';
        $leave->remarks = $rejectionReason; // Save the remarks to the model
        $leave->save();

        // --- Notify student ---
        $student = $leave->student;
        if ($student) {
            $statusMessage = 'Rejected by HOD';
            $actorName = Auth::user()->name . " (HOD)";

            try {
                // Pass the captured $rejectionReason to the notification
                $student->notify(new LeaveStatusUpdatedNotification($leave, $student, $statusMessage, $actorName, $rejectionReason));
            } catch (\Exception $e) {
                Log::error("Failed to send HOD leave rejection email to {$student->email}: " . $e->getMessage());
                return redirect()->back()->with('error', 'Leave rejected, but notification to student failed. Please check logs.');
            }
        } else {
            Log::error("Could not find student for leave ID: {$leave->id} during HOD rejection.");
        }

        return redirect()->back()->with('error', 'Leave rejected and student notified.');
    }

    // Added studentHistory method
    public function studentHistory()
    {
        $hod = Auth::user();
        if (!$hod || !$hod->department_id) {
            return redirect()->route('hod.dashboard') // Redirect if HOD/department invalid
                             ->with('error', 'Unable to determine your department to view history.');
        }
        $departmentId = $hod->department_id;

        $leavesHistory = Leave::whereHas('student', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20); // Using pagination

        // Ensure the view file 'resources/views/hod/student-history.blade.php' exists
        return view('hod.student-history', compact('leavesHistory'));
    }

} // End of HodController class
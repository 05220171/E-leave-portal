<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Notifications\LeaveStatusUpdatedNotification;
use Illuminate\Support\Facades\Auth; // To get SSO's name
use Illuminate\Support\Facades\Log;   // For error logging


class SSOController extends Controller
{
    /**
     * Display all leaves with status 'awaiting_sso_approval' regardless of department.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Eager load the student relationship for use in the view
        $leaves = Leave::with('student')
                    ->where('status', 'awaiting_sso_approval')
                    ->get();

        return view('sso.dashboard', compact('leaves'));
    }

    /**
     * Approve the leave request by the SSO.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveLeave(Request $request, $id) // Added Request for consistency, can be removed if no input
    {
        $leave = Leave::findOrFail($id);
        $leave->status = 'approved';
        $leave->save();

        // --- Notify student ---
        $student = $leave->student;
        if ($student) {
            $statusMessage = 'Approved';
            $actorName = Auth::user()->name . " (SSO)";

            try {
                // Pass null for rejectionReason on approval
                $student->notify(new LeaveStatusUpdatedNotification($leave, $student, $statusMessage, $actorName, null));
            } catch (\Exception $e) {
                Log::error("Failed to send SSO leave approval email to {$student->email}: " . $e->getMessage());
                return redirect()->back()->with('success', 'Leave approved, but notification to student failed. Please check logs.');
            }
        } else {
            Log::error("Could not find student for leave ID: {$leave->id} during SSO approval.");
        }

        return redirect()->back()->with('success', 'Leave approved successfully and student notified.');
    }

    public function rejectLeave(Request $request, $id) // Ensure Request $request is here
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000', // Add validation for remarks
        ]);

        $leave = Leave::findOrFail($id);
        $rejectionReason = $request->input('remarks'); // Get remarks from the request

        $leave->status = 'rejected_by_sso';
        $leave->remarks = $rejectionReason; // Save the remarks to the model
        $leave->save();

        // --- Notify student ---
        $student = $leave->student;
        if ($student) {
            $statusMessage = 'Rejected by SSO';
            $actorName = Auth::user()->name . " (SSO)";

            try {
                // Pass the captured $rejectionReason to the notification
                $student->notify(new LeaveStatusUpdatedNotification($leave, $student, $statusMessage, $actorName, $rejectionReason));
            } catch (\Exception $e) {
                Log::error("Failed to send SSO leave rejection email to {$student->email}: " . $e->getMessage());
                return redirect()->back()->with('error', 'Leave rejected, but notification to student failed. Please check logs.');
            }
        } else {
            Log::error("Could not find student for leave ID: {$leave->id} during SSO rejection.");
        }
        return redirect()->back()->with('error', 'Leave rejected and student notified.');
    }
}
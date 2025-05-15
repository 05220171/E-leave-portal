<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Notifications\LeaveStatusUpdatedNotification;
use Illuminate\Support\Facades\Auth; // To get DSA's name
use Illuminate\Support\Facades\Log;   // For error logging


class DsaController extends Controller
{
    public function __construct()
    {
        // Middleware like 'role:dsa' is applied in web.php route group
    }

    public function index()
    {
        $leaves = Leave::where('status', 'awaiting_dsa_approval')->with('student')->latest()->get();
        return view('dsa.dashboard', compact('leaves'));
    }

    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->status = 'awaiting_sso_approval';

        // Save optional remarks from the request
        // if ($request->has('remarks')) {
        //     $leave->remarks = $request->input('remarks');
        // }

        $leave->save();

        return redirect()->route('dsa.dashboard')->with('success', 'Leave approved successfully and sent to SSO.');
    }

    public function reject(Request $request, $id) // Ensure Request $request is here
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000', // Add validation for remarks
        ]);

        $leave = Leave::findOrFail($id);
        $rejectionReason = $request->input('remarks'); // Get remarks from the request

        $leave->status = 'rejected_by_dsa';
        $leave->remarks = $rejectionReason; // Save the remarks to the model
        $leave->save();

        // --- Notify student ---
        $student = $leave->student;
        if ($student) {
            $statusMessage = 'Rejected by DSA';
            $actorName = Auth::user()->name . " (DSA)";

            try {
                // Pass the captured $rejectionReason to the notification
                $student->notify(new LeaveStatusUpdatedNotification($leave, $student, $statusMessage, $actorName, $rejectionReason));
            } catch (\Exception $e) {
                Log::error("Failed to send DSA leave rejection email to {$student->email}: " . $e->getMessage());
                return redirect()->route('dsa.dashboard')->with('error', 'Leave rejected, but notification to student failed. Please check logs.');
            }
        } else {
            Log::error("Could not find student for leave ID: {$leave->id} during DSA rejection.");
        }
        return redirect()->route('dsa.dashboard')->with('error', 'Leave rejected and student notified.');
    }
}

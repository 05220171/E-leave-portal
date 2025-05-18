<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User; // For the student
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Header\UnstructuredHeader; // <<< ADD THIS
use Symfony\Component\Mime\Header\ParameterizedHeader;

class NewLeaveRequestForYourApproval extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public string $approverRoleTitle; // e.g., "HOD", "DSA"
    public ?string $leaveThreadId;    // For email threading

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Leave $leave The leave request object
     * @param string $approverRole The role of the recipient (e.g., 'hod')
     */
    public function __construct(Leave $leave)
    {
        $this->leave = $leave->loadMissing('type', 'student.department'); // Load necessary relationships
        $this->student = $this->leave->student;
        $this->approverRoleTitle = Str::title(str_replace('_', ' ', $this->leave->current_approver_role));
        $this->leaveThreadId = $this->leave->email_thread_id; // Get the stored thread ID
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'helpdesk.e-leave.jnec@rub.edu.bt'), env('MAIL_FROM_NAME', config('app.name'))),
            subject: 'New Leave Request for Your Approval: ' . $this->student->name,
            // MODIFIED WAY TO ADD HEADERS
            
        );
    }

    public function content(): Content
    {
        $dashboardRouteName = strtolower($this->leave->current_approver_role ?? 'login') . '.dashboard';
        $actionUrl = route($dashboardRouteName, [], false);
        if (!\Illuminate\Support\Facades\Route::has($dashboardRouteName)) {
            $actionUrl = route('login');
        }

        return new Content(
            markdown: 'emails.leaves.new_request_for_approval',
            with: [
                'actionUrl' => $actionUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
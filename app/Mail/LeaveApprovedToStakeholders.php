<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User; // For student and potentially the approver
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveApprovedToStakeholders extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public User $approvingUser; // The DSA who approved it

    /**
     * Create a new message instance.
     */
    public function __construct(Leave $leave, User $approvingUser)
    {
        $this->leave = $leave;
        $this->student = $leave->student;
        $this->approvingUser = $approvingUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS', 'helpdesk.e-leave.jnec@rub.edu.bt'),
            subject: 'Leave Approved for Student: ' . $this->student->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.approved_stakeholders',
            // No specific URL needed for stakeholders unless you have an admin view for them
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
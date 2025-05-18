<?php

namespace App\Mail;

use App\Models\Leave; // Your Leave model
use App\Models\User;  // Your User model (for student)
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveApprovedFinalToStudent extends Mailable implements ShouldQueue // Optional: for queueing emails
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;

    /**
     * Create a new message instance.
     */
    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
        $this->student = $leave->student; // Assuming student relationship is loaded or accessible
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS', 'helpdesk.e-leave.jnec@rub.edu.bt'), // Get from .env or default
            subject: 'Leave Request Approved: ' . $this->leave->type->name, // Dynamic subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.approved_final_student', // Path to your Markdown template
            with: [ // Data to pass to the view
                'leaveUrl' => route('student.leave-history'), // Example URL, adjust as needed
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
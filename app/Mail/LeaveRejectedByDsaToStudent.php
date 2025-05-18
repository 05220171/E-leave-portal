<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRejectedByDsaToStudent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public ?string $rejectionRemarks; // Can be null

    /**
     * Create a new message instance.
     */
    public function __construct(Leave $leave, ?string $rejectionRemarks)
    {
        $this->leave = $leave;
        $this->student = $leave->student;
        $this->rejectionRemarks = $rejectionRemarks;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
            subject: 'Leave Request Update: ' . $this->leave->type->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.rejected_dsa_student',
            with: [
                'leaveUrl' => route('student.leave-history'),
            ],
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
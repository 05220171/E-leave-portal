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
use Illuminate\Support\Str;

class LeaveForwardedToNextApproverFromDsa extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public string $nextApproverRoleTitle;
    public bool $isForStudent; // To differentiate content for student vs next approver

    /**
     * Create a new message instance.
     */
    public function __construct(Leave $leave, string $nextApproverRole, bool $isForStudent = true)
    {
        $this->leave = $leave;
        $this->student = $leave->student;
        $this->nextApproverRoleTitle = Str::title(str_replace('_', ' ', $nextApproverRole));
        $this->isForStudent = $isForStudent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isForStudent ?
                   'Leave Request Progress: Forwarded to ' . $this->nextApproverRoleTitle :
                   'Leave Request for Approval: ' . $this->student->name;

        return new Envelope(
            from: env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.forwarded_from_dsa',
            with: [
                // URL for student to view history, or for approver to view dashboard
                'actionUrl' => $this->isForStudent ? route('student.leave-history') : route(strtolower($this->leave->current_approver_role).'.dashboard'),
                'actionText' => $this->isForStudent ? 'View Leave History' : 'Review Leave Request',
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
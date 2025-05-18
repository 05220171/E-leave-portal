<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User; // For student and the approver who acted
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class LeaveApprovedByFirstApproverToStudent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public User $actingApprover; // The HOD, DSA, etc. who just approved
    public string $actingApproverRoleTitle;
    public string $nextApproverRoleTitle;
    public ?string $leaveThreadId; // For email threading

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Leave $leave The leave request object
     * @param \App\Models\User $actingApprover The user who just approved the leave
     * @param string $nextApproverRole The role of the next approver in the chain (e.g., 'dsa')
     */
    public function __construct(Leave $leave, User $actingApprover, string $nextApproverRole)
    {
        $this->leave = $leave->loadMissing('type', 'student');
        $this->student = $this->leave->student;
        $this->actingApprover = $actingApprover;
        $this->actingApproverRoleTitle = Str::title(str_replace('_', ' ', $actingApprover->role)); // Or $leave->approvalActions->last()->acted_as_role
        $this->nextApproverRoleTitle = Str::title(str_replace('_', ' ', $nextApproverRole));
        $this->leaveThreadId = $this->leave->email_thread_id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@example.com'), env('MAIL_FROM_NAME', config('app.name'))),
            to: [$this->student->email],
            subject: 'Leave Update: Approved by ' . $this->actingApproverRoleTitle . ' & Forwarded',
            // MODIFIED WAY TO ADD HEADERS
            
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.approved_by_first_approver_to_student',
            with: [
                'leaveUrl' => route('student.leave-history'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
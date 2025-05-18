<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Header\UnstructuredHeader; // <<< ENSURE THIS IS PRESENT

class LeaveRejectedToStudent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public User $rejectingApprover;
    public string $rejectingApproverRoleTitle;
    public ?string $rejectionRemarks;
    public ?string $leaveThreadId;

    public function __construct(Leave $leave, User $rejectingApprover, ?string $rejectionRemarks)
    {
        $this->leave = $leave->loadMissing('type', 'student');
        $this->student = $this->leave->student;
        $this->rejectingApprover = $rejectingApprover;
        $this->rejectingApproverRoleTitle = Str::title(str_replace('_', ' ', $rejectingApprover->role));
        $this->rejectionRemarks = $rejectionRemarks;
        $this->leaveThreadId = $this->leave->email_thread_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@example.com'), env('MAIL_FROM_NAME', config('app.name'))),
            to: [$this->student->email],
            subject: 'Leave Request Update: Rejected',
            // MODIFIED WAY TO ADD HEADERS
            
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.request_rejected_to_student',
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
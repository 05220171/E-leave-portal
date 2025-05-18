<?php

namespace App\Mail;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address; // For From address
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class LeaveRequestSubmittedToStudent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public string $firstApproverRoleTitle;
    public string $initialMessageId; // For email threading

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Leave $leave The leave request object
     */
    public function __construct(Leave $leave)
    {
        $this->leave = $leave->loadMissing('type', 'student'); // Ensure relationships are loaded
        $this->student = $this->leave->student;
        $this->firstApproverRoleTitle = Str::title(str_replace('_', ' ', $this->leave->current_approver_role ?? 'Admin'));

        // Generate a unique Message-ID for this initial email
        // This will be stored and used for threading subsequent emails for THIS leave request.
        $this->initialMessageId = '<' . Str::uuid()->toString() . '@' . parse_url(config('app.url'), PHP_URL_HOST) . '>';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'helpdesk.e-leave.jnec@rub.edu.bt'), env('MAIL_FROM_NAME', config('app.name'))),
            to: [$this->student->email],
            subject: 'Leave Request Submitted: ' . ($this->leave->type->name ?? 'Leave'),
            // MODIFIED WAY TO ADD HEADERS
            
        );
    }
            

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leaves.request_submitted_to_student',
            with: [
                'leaveUrl' => route('student.leave-history'), // Or a specific status link
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
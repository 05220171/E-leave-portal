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
//use Symfony\Component\Mime\Header\UnstructuredHeader; // <<< ENSURE THIS IS PRESENT

class LeaveApprovedForYourRecords extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public User $student;
    public User $finalDecisionMaker;
    public string $recordKeeperRoleTitle;
    
     /**
     * Create a new message instance.
     * @param \App\Models\Leave $leave
     * @param \App\Models\User $finalDecisionMaker The user who made the final decision
     * @param string $recordKeeperRole The role string of the recipient (e.g., 'sso')
     */

    public function __construct(Leave $leave, User $finalDecisionMaker, string $recordKeeperRole)
    {
        $this->leave = $leave->loadMissing('type', 'student.department');
        $this->student = $this->leave->student;
        $this->finalDecisionMaker = $finalDecisionMaker;
        $this->recordKeeperRoleTitle = Str::title(str_replace('_', ' ', $recordKeeperRole));
        
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'helpdesk.e-leave.jnec@rub.edu.bt'), env('MAIL_FROM_NAME', config('app.name'))),
            subject: 'Approved Leave for Record: ' . $this->student->name,
            // MODIFIED WAY TO ADD HEADERS
            
        );
    }

    public function content(): Content
    {
        $actionUrl = route(strtolower($this->recordKeeperRoleTitle).'.dashboard'); // Assumes an SSO or generic record keeper dashboard route
        // if (!\Illuminate\Support\Facades\Route::has(strtolower($this->recordKeeperRoleTitle).'.dashboard')) {
        //      $actionUrl = route('login'); // Fallback
        // }

        return new Content(
            markdown: 'emails.leaves.approved_for_your_records',
            with: [
                'actionUrl' => $actionUrl,
            ]
        );

    }

    public function attachments(): array
    {
        return [];
    }
}
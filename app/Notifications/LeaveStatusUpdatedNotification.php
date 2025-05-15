<?php

namespace App\Notifications;

use App\Models\Leave; // Your Leave model
use App\Models\User;  // Your User model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdatedNotification extends Notification implements ShouldQueue // Recommended: Implement ShouldQueue
{
    use Queueable;

    public Leave $leave;
    public User $student;
    public string $statusMessage; // e.g., "Approved", "Rejected by HOD"
    public string $actorName;     // Name of HOD, DSA, or SSO who took the action
    public ?string $rejectionReason; // Optional rejection reason

    /**
     * Create a new notification instance.
     *
     * @param Leave $leave
     * @param User $student
     * @param string $statusMessage
     * @param string $actorName
     * @param string|null $rejectionReason
     */
    public function __construct(Leave $leave, User $student, string $statusMessage, string $actorName, ?string $rejectionReason = null)
    {
        $this->leave = $leave;
        $this->student = $student;
        $this->statusMessage = $statusMessage;
        $this->actorName = $actorName;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // Send via email
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable The entity being notified (the student User model)
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = new MailMessage;
        $subject = '';
        $greeting = 'Dear ' . $this->student->name . ',';

        $leaveDetails = [
            "Request ID: " . $this->leave->id,
            "Leave Type: " . ucfirst($this->leave->leave_type),
            "From Date: " . $this->leave->start_date->format('d M, Y'),
            "To Date: " . $this->leave->end_date->format('d M, Y'),
            "Number of Days: " . $this->leave->number_of_days,
        ];

        if (strtolower($this->statusMessage) === 'approved') {
            $subject = 'Your Leave Request has been Approved';
            $mailMessage
                ->subject($subject)
                ->greeting($greeting)
                ->line('Congratulations! Your leave request has been approved.')
                ->line("Approved by: " . $this->actorName . ".");

            foreach ($leaveDetails as $detail) {
                $mailMessage->line($detail);
            }

            $mailMessage->action('View Leave History', route('student.leave-history'));

        } elseif (str_contains(strtolower($this->statusMessage), 'rejected')) {
            $subject = 'Your Leave Request has been Rejected';
            $mailMessage
                ->subject($subject)
                ->greeting($greeting)
                ->error() // Use error styling for rejections
                ->line('We regret to inform you that your leave request has been rejected.')
                ->line("Status: " . $this->statusMessage . ".") // e.g., "Rejected by HOD"
                ->line("Rejected by: " . $this->actorName . ".");

            if ($this->rejectionReason) {
                $mailMessage->line("Reason for rejection: " . $this->rejectionReason);
            }

            foreach ($leaveDetails as $detail) {
                $mailMessage->line($detail);
            }
            $mailMessage->action('View Leave History', route('student.leave-history'));
        } else {
            // Fallback for other potential status updates (if you ever need them)
            // For now, your logic is focused on final approval/rejection.
            $subject = 'Leave Request Status Update';
            $mailMessage
                ->subject($subject)
                ->greeting($greeting)
                ->line('Your leave request status has been updated: ' . $this->statusMessage)
                ->line("Updated by: " . $this->actorName . ".");

            foreach ($leaveDetails as $detail) {
                $mailMessage->line($detail);
            }
            $mailMessage->action('View Leave History', route('student.leave-history'));
        }

        $mailMessage->salutation('Regards,' . "\n" . config('app.name'));

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     * (For database notifications, broadcasting, etc.)
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'leave_id' => $this->leave->id,
            'student_name' => $this->student->name,
            'status_message' => $this->statusMessage,
            'actor_name' => $this->actorName,
            'rejection_reason' => $this->rejectionReason,
            'action_url' => route('student.leave-history'),
        ];
    }
}
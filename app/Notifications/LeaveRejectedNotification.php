<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRejectedNotification extends Notification
{
    use Queueable;

    public $rejectedBy;

    public function __construct($rejectedBy)
    {
        $this->rejectedBy = $rejectedBy;
    }

    public function via($notifiable)
    {
        return ['mail']; // Or 'database' if you want to save it in the database
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Leave Application Status')
            ->line('Your leave application has been rejected by ' . $this->rejectedBy)
            ->action('View Leave History', url('/student/leave-history'))
            ->line('Thank you for using the e-leave portal!');
    }

    // If you also want to store this in the database
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your leave application has been rejected by ' . $this->rejectedBy
        ];
    }
}

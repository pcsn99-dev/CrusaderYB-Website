<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $temporaryPassword
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your CYB Admin Account Temporary Password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An admin account has been created for you in the CYB Admin Portal.')
            ->line('Use the temporary password below to log in:')
            ->line($this->temporaryPassword)
            ->line('You will be required to change your password after logging in.')
            ->line('This temporary password will expire in 3 days.')
            ->line('If you did not expect this account, please contact the system administrator.');
    }
}
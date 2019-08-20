<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;


class BorrowerAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to '. config('app.name'))
            ->greeting("Hi! {$notifiable->firstname},")
            ->line('Welcome to ' . config('app.name') . ', an application that helps you to access loans from ' .
                'multiple banks')
            ->line('To complete your account registration, click the link below')
            ->action('Verify Your Account', '')
            ->line('Thank you for using our application!');
    }
}

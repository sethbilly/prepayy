<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class AccountOwnerCreated extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var string
     */
    private $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token the password reset token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $expiry = config('auth.passwords.users.expire')/60;

        $pluralizedExpiry = str_plural('hour', $expiry);

        $note = <<<NOTE
<ul style="margin-top: 0; color: #74787E; font-size: 16px; line-height: 1.5em;">
    <li>The reset link expires after {$expiry} {$pluralizedExpiry}</li>
    <li>Your account password must be at least 8 characters long</li>
    <li>Do not share your account password with anyone else</li>
    <li>If your account password ever gets compromised, endeavour to promptly change it</li>
</ul>
NOTE;

        return (new MailMessage)
            ->subject(sprintf(
                'Invitation to administer %s on %s', $notifiable->institutable->name, config('app.name')
            ))
            ->greeting("Hi {$notifiable->firstname},")
            ->line("You have been invited to administer {$notifiable->institutable->name} on " . config('app.name'))
            ->line('To complete your account setup process, click the button below and from the page you are ' .
                'redirected to, set a new account password')
            ->action('Set Account Password', route('password.reset.get', [
                'token' => $this->token, 'email' => urlencode($notifiable->email)
            ]))
            ->line('Things to Note: ')
            ->line($note);
    }
}

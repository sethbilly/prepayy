<?php

namespace App\Notifications;

use App\Entities\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PartnerApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var string
     */
    private $token;
    /**
     * @var LoanApplication
     */
    private $application;

    /**
     * Create a new notification instance.
     *
     * @param LoanApplication $application
     * @param string $token
     */
    public function __construct(LoanApplication $application, string $token)
    {
        $this->token = $token;
        $this->application = $application;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('app.name') . ': Loan Application Submission Token')
            ->greeting("Hi! {$notifiable->firstname},")
            ->line(sprintf('Your submission token for %s is %s', $this->application->loanProduct->name, $this->token));
    }
}

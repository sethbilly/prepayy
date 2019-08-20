<?php

namespace App\Notifications;

use App\Entities\LoanApplication;
use App\Entities\User;
use CloudLoan\Traits\ApprovesLoanNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoanDeclinedByEmployer extends Notification implements ShouldQueue
{
    use Queueable, ApprovesLoanNotification;
    /**
     * @var LoanApplication
     */
    private $application;
    /**
     * @var User
     */
    private $approver;

    /**
     * Create a new notification instance.
     *
     * @param LoanApplication $application
     * @param User $approver
     */
    public function __construct(LoanApplication $application, User $approver)
    {
        $this->application = $application;
        $this->approver = $approver;
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
            ->subject($this->getDeclinedSubject())
            ->greeting($this->getGreeting($notifiable))
            ->line(sprintf(
                '%s has declined your application for a %s loan from %s',
                $this->approver->institutable->name,
                $this->application->loanProduct->name,
                $this->application->loanProduct->institution->name
            ))
            ->line('To review the application or the reason for the decline, click the link below')
            ->action($this->getActionText(), $this->getActionLink($this->application))
            ->line($this->getLoginPrompt())
            ->line($this->getSignature());
    }
}

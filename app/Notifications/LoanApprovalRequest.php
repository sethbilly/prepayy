<?php

namespace App\Notifications;

use App\Entities\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoanApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var LoanApplication
     */
    private $application;

    /**
     * Create a new notification instance.
     *
     * @param LoanApplication $application
     */
    public function __construct(LoanApplication $application)
    {
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
            ->subject(config('app.name') . ': Request for Loan Application Approval')
            ->greeting("Hi! {$notifiable->firstname},")
            ->line(sprintf('%s\'s request for a %s from %s is awaiting your approval',
                    $this->application->user->getFullName(),
                    $this->application->loanProduct->name,
                    $this->application->loanProduct->institution->name)
            )
            ->line('To review the loan application and approve or decline it, click the link below')
            ->line('Kindly note that you will be required to login to your ' .
                config('app.name') . ' account to perform this action')
            ->action('Approve Loan', route('loan_applications.show', ['application' => $this->application]))
            ->line('Note: You will be required to login to your ' . config('app.name') . ' account')
            ->line('Thank you for using ' . config('app.name') . '!');
    }
}

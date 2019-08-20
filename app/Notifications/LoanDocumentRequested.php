<?php

namespace App\Notifications;

use App\Entities\RequestedLoanDocument;
use CloudLoan\Traits\ApprovesLoanNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanDocumentRequested extends Notification implements ShouldQueue
{
    use Queueable, ApprovesLoanNotification;
    /**
     * @var RequestedLoanDocument
     */
    private $document;

    /**
     * Create a new notification instance.
     *
     * @param RequestedLoanDocument $document
     */
    public function __construct(RequestedLoanDocument $document)
    {
        $this->document = $document;
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
            ->subject(config('app.name') . ': Request for Loan Document')
            ->greeting("Hi! {$notifiable->firstname}")
            ->line(sprintf('%s is requesting for additional documents for your application for %s',
                    $this->document->application->loanProduct->institution->name,
                    $this->document->application->loanProduct->name)
            )
            ->line('The request is as follows: ')
            ->line($this->document->request)
            ->line('Click the button below to review your application and respond to the request')
            ->action('Send Documents', route('loan_applications.edit', [
                'application' => $this->document->application
            ]))
            ->line($this->getLoginPrompt())
            ->line($this->getSignature());
    }
}

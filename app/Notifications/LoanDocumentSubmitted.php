<?php

namespace App\Notifications;

use App\Entities\RequestedLoanDocument;
use CloudLoan\Traits\ApprovesLoanNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanDocumentSubmitted extends Notification implements ShouldQueue
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
            ->subject(config('app.name') . ': Loan Document Submitted')
            ->greeting("Hi! {$notifiable->firstname},")
            ->line(sprintf('%s has responded to your request for additional documents for %s',
                    $this->document->application->user->getFullName(),
                    $this->document->application->loanProduct->name
                )
            )
            ->line('To review the application or view the submitted documents, click the link below')
            ->action($this->getActionText(), $this->getActionLink($this->document->application))
            ->line($this->getLoginPrompt())
            ->line($this->getSignature());
    }
}

<?php

namespace App\Notifications;

use App\Models\Quotation;
use App\Support\Inr;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email to the studio's address when a client accepts. Queued, so a mail server problem never blocks the client.
 */
class QuotationAcceptedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quotation $quotation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $quotation = $this->quotation;

        return (new MailMessage)
            ->subject("Accepted: {$quotation->number} · {$quotation->displayName()}")
            ->greeting('Good news!')
            ->line("{$quotation->signed_name} accepted quotation {$quotation->number} for {$quotation->displayName()}.")
            ->line('Grand total: '.Inr::format($quotation->cached_grand_total))
            ->line('Dates: '.$quotation->datesLabel())
            ->action('Open the quotation', route('studio.quotations.edit', $quotation))
            ->line('The quotation is now locked. Unlock it from the editor if you need to make changes.');
    }
}

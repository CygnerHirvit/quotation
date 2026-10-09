<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-app notice for the studio team: the client opened the quotation link for the first time.
 */
class QuotationViewed extends Notification
{
    use Queueable;

    public function __construct(public Quotation $quotation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'viewed',
            'quotation_id' => $this->quotation->id,
            'number' => $this->quotation->number,
            'client' => $this->quotation->displayName(),
            'message' => "{$this->quotation->displayName()} opened {$this->quotation->number}",
        ];
    }
}

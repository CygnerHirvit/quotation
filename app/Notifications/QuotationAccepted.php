<?php

namespace App\Notifications;

use App\Models\Quotation;
use App\Support\Inr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-app notice for the studio team: the client accepted and signed the quotation.
 */
class QuotationAccepted extends Notification
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
            'kind' => 'accepted',
            'quotation_id' => $this->quotation->id,
            'number' => $this->quotation->number,
            'client' => $this->quotation->displayName(),
            'message' => "{$this->quotation->signed_name} accepted {$this->quotation->number} (".Inr::format($this->quotation->cached_grand_total).')',
        ];
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A crew line on one event. Name and rate are copied from the price list so later price changes don't affect it.
 */
#[Fillable(['quotation_event_id', 'price_item_id', 'name', 'qty', 'rate', 'sort_order'])]
class QuotationEventCrew extends Model
{
    use HasSortOrder;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'rate' => 'integer',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['quotation_event_id'];
    }

    /**
     * @return BelongsTo<QuotationEvent, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(QuotationEvent::class, 'quotation_event_id');
    }
}

<?php

namespace App\Models;

use App\Enums\ItemSection;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A video or photo deliverable on a quotation. Items that are not included are left out of totals and hidden from the client.
 */
#[Fillable(['quotation_id', 'price_item_id', 'section', 'name', 'rate', 'qty', 'note', 'delivery', 'included', 'sort_order'])]
class QuotationItem extends Model
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
            'section' => ItemSection::class,
            'qty' => 'integer',
            'rate' => 'integer',
            'included' => 'boolean',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['quotation_id', 'section'];
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}

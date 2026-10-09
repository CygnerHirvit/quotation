<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A term copied onto one quotation from the studio's term templates.
 */
#[Fillable(['quotation_id', 'title', 'body', 'sort_order'])]
class QuotationTerm extends Model
{
    use HasSortOrder;

    protected function sortGroupColumns(): array
    {
        return ['quotation_id'];
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}

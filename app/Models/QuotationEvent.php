<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One function of a quotation (Engagement, Sangeet, Wedding…) with its own date and crew.
 */
#[Fillable(['quotation_id', 'name', 'event_date', 'venue', 'note', 'sort_order'])]
class QuotationEvent extends Model
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
            'event_date' => 'date',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['quotation_id'];
    }

    /**
     * Crew for the client: same names merged with their counts, rates left out, e.g. ["2 × Candid Photographer"].
     *
     * @return list<array{name: string, qty: int}>
     */
    public function crewSummary(): array
    {
        return $this->crew
            ->groupBy('name')
            ->map(fn ($lines, string $name) => ['name' => $name, 'qty' => (int) $lines->sum('qty')])
            ->values()
            ->all();
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * @return HasMany<QuotationEventCrew, $this>
     */
    public function crew(): HasMany
    {
        return $this->hasMany(QuotationEventCrew::class)->ordered();
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStudio;
use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['studio_id', 'title', 'tagline', 'position', 'is_active', 'sort_order'])]
class CustomSection extends Model
{
    use BelongsToStudio, HasFactory, HasSortOrder;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['studio_id', 'position'];
    }

    /**
     * Scope custom sections ordered in natural quotation sequence and relative sort_order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->orderByRaw("CASE position 
            WHEN 'after_cover' THEN 1 
            WHEN 'after_coverage' THEN 2 
            WHEN 'after_deliverables' THEN 3 
            WHEN 'after_price' THEN 4 
            ELSE 5 END")
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return HasMany<CustomSectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CustomSectionItem::class)->ordered();
    }

    /**
     * Anchor identifier for links / table-of-contents in quotation.
     */
    public function anchorId(): string
    {
        return 'custom-section-'.$this->id;
    }
}


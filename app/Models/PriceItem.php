<?php

namespace App\Models;

use App\Enums\PriceCategory;
use App\Models\Concerns\BelongsToStudio;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PriceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['studio_id', 'category', 'name', 'internal_label', 'rate', 'note', 'delivery', 'is_active', 'sort_order'])]
class PriceItem extends Model
{
    /** @use HasFactory<PriceItemFactory> */
    use BelongsToStudio, HasFactory, HasSortOrder;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => PriceCategory::class,
            'rate' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['studio_id', 'category'];
    }
}

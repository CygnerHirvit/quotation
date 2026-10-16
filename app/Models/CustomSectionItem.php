<?php

namespace App\Models;

use App\Models\Concerns\HasSortOrder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['custom_section_id', 'title', 'description', 'is_active', 'sort_order'])]
class CustomSectionItem extends Model
{
    use HasFactory, HasSortOrder;

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
        return ['custom_section_id'];
    }

    /**
     * @return BelongsTo<CustomSection, $this>
     */
    public function customSection(): BelongsTo
    {
        return $this->belongsTo(CustomSection::class);
    }
}


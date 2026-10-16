<?php

namespace App\Models;

use App\Enums\PortfolioType;
use App\Models\Concerns\BelongsToStudio;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\PortfolioItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['studio_id', 'type', 'title', 'url', 'cover_path', 'is_active', 'sort_order'])]
class PortfolioItem extends Model
{
    /** @use HasFactory<PortfolioItemFactory> */
    use BelongsToStudio, HasFactory, HasSortOrder;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PortfolioType::class,
            'is_active' => 'boolean',
        ];
    }

    protected function sortGroupColumns(): array
    {
        return ['studio_id', 'type'];
    }

    /**
     * The uploaded cover, or for YouTube videos the video's own thumbnail.
     */
    public function coverUrl(): ?string
    {
        if ($this->cover_path && Storage::disk('public')->exists($this->cover_path)) {
            return asset('storage/'.$this->cover_path);
        }

        $youtubeId = $this->youtubeId();

        return $youtubeId ? "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg" : null;
    }

    public function youtubeId(): ?string
    {
        $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $this->url, $matches) ? $matches[1] : null;
    }
}

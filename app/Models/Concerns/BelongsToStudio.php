<?php

namespace App\Models\Concerns;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Keeps studio-owned records private to their studio.
 *
 * When a studio user is signed in, every query is limited to that user's studio
 * and new records are stamped with the studio automatically. Super admins and
 * guests (console, public client pages) are not scoped.
 */
trait BelongsToStudio
{
    public static function bootBelongsToStudio(): void
    {
        static::addGlobalScope('studio', function (Builder $builder): void {
            $studioId = static::currentStudioId();

            if ($studioId !== null) {
                $builder->where($builder->getModel()->qualifyColumn('studio_id'), $studioId);
            }
        });

        static::creating(function (Model $model): void {
            if ($model->getAttribute('studio_id') === null) {
                $model->setAttribute('studio_id', static::currentStudioId());
            }
        });
    }

    /**
     * @return BelongsTo<Studio, $this>
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    protected static function currentStudioId(): ?int
    {
        $user = Auth::user();

        if ($user === null || $user->isSuperAdmin()) {
            return null;
        }

        return $user->studio_id;
    }
}

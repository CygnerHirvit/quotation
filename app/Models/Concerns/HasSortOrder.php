<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Manual ordering inside a group (e.g. price items inside one category of one studio).
 *
 * Models define sortGroupColumns() with the columns that form the group.
 * New records go to the end of their group.
 */
trait HasSortOrder
{
    /**
     * @return list<string>
     */
    abstract protected function sortGroupColumns(): array;

    public static function bootHasSortOrder(): void
    {
        static::creating(function (Model $model): void {
            if (! $model->sort_order) {
                $model->sort_order = (int) $model->sortSiblings()->max('sort_order') + 1;
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Records in the same group as this one, including itself.
     *
     * @return Builder<static>
     */
    public function sortSiblings(): Builder
    {
        $query = static::query();

        foreach ($this->sortGroupColumns() as $column) {
            $query->where($column, $this->getRawOriginal($column) ?? $this->getAttributes()[$column] ?? null);
        }

        return $query;
    }

    /**
     * Move this record to a zero-based position in its group and renumber the group.
     */
    public function moveToPosition(int $position): void
    {
        DB::transaction(function () use ($position): void {
            $ids = $this->sortSiblings()->ordered()->pluck('id')->reject(fn (int $id) => $id === $this->id)->values();

            $ids->splice(max(0, min($position, $ids->count())), 0, [$this->id]);

            foreach ($ids as $index => $id) {
                static::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}

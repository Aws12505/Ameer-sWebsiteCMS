<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * A "Published" switch, plus the date of the first publish that locks the
 * record's key (its URL slug) so links on the live site never break.
 */
trait HasPublishing
{
    /**
     * Boot the trait.
     */
    public static function bootHasPublishing(): void
    {
        static::saving(function (self $model): void {
            if ($model->published && $model->first_published_at === null) {
                $model->first_published_at = now();
            }
        });
    }

    /**
     * Initialize the trait.
     */
    public function initializeHasPublishing(): void
    {
        $this->mergeCasts([
            'published' => 'boolean',
            'first_published_at' => 'datetime',
        ]);
    }

    /**
     * Whether the key can no longer change because the item went live once.
     */
    public function isKeyLocked(): bool
    {
        return $this->first_published_at !== null;
    }

    /**
     * Only published records.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('published'), true);
    }
}

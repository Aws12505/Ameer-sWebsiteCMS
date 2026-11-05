<?php

namespace App\Concerns;

use App\Support\FrontendRevalidator;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Manager-controlled order through a `sort_order` column.
 */
trait IsSortable
{
    /**
     * Boot the trait.
     */
    public static function bootIsSortable(): void
    {
        static::creating(function (self $model): void {
            if ($model->getAttribute('sort_order')) {
                return;
            }

            $query = static::query()->withoutGlobalScopes();

            $model->sort_order = $model->addsNewItemsFirst()
                ? (int) $query->min('sort_order') - 1
                : (int) $query->max('sort_order') + 1;
        });
    }

    /**
     * Whether new items go to the top of the list instead of the bottom.
     */
    public function addsNewItemsFirst(): bool
    {
        return false;
    }

    /**
     * Order records the way the manager arranged them.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('id'));
    }

    /**
     * Put the given records in the given order.
     *
     * The records keep the positions they already occupy, so re-ordering a
     * filtered subset (for example only the Residential services) never
     * moves the records that are not part of it.
     *
     * @param  list<int>  $ids
     */
    public static function reorder(array $ids): void
    {
        $positions = static::query()
            ->whereKey($ids)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('sort_order')
            ->all();

        if ($positions === [] || count($positions) !== count($ids)) {
            return;
        }

        if (count(array_unique($positions)) !== count($positions)) {
            $start = (int) min($positions);
            $positions = range($start, $start + count($ids) - 1);
        }

        DB::transaction(function () use ($ids, $positions): void {
            foreach ($ids as $index => $id) {
                static::query()->whereKey($id)->update(['sort_order' => $positions[$index]]);
            }
        });

        app(FrontendRevalidator::class)->queue(static::query()->getModel()->revalidationTags());
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    abstract public function revalidationTags(): array;
}

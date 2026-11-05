<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Deleting moves a record to the trash, where it can be restored for
 * a while before the scheduled "model:prune" command removes it.
 *
 * @mixin Model
 */
trait PrunesTrash
{
    use MassPrunable, SoftDeletes;

    /**
     * The trashed records that are old enough to remove for good.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where($this->getQualifiedDeletedAtColumn(), '<=', now()->subDays(config('cms.trash_days')));
    }
}

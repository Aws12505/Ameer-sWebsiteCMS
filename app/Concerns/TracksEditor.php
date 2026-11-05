<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Records who saved a record last ("last edited by / at").
 *
 * @mixin Model
 */
trait TracksEditor
{
    /**
     * Boot the trait.
     */
    public static function bootTracksEditor(): void
    {
        static::saving(function (self $model): void {
            $user = Auth::user();

            if ($user instanceof User) {
                $model->updated_by = $user->id;
            }
        });
    }

    /**
     * The user who saved the record last.
     *
     * @return BelongsTo<User, $this>
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The "last edited" details shown in the admin panel.
     *
     * @return array{by: string|null, at: string|null}
     */
    public function lastEdited(): array
    {
        return [
            'by' => $this->editor?->name,
            'at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

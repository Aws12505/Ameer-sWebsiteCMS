<?php

namespace App\Concerns;

use App\Support\FrontendRevalidator;

/**
 * Asks the front end to refresh its cache after the record changes.
 */
trait RevalidatesFrontend
{
    /**
     * Boot the trait.
     */
    public static function bootRevalidatesFrontend(): void
    {
        $queue = fn (self $model) => app(FrontendRevalidator::class)->queue($model->revalidationTags());

        static::saved($queue);
        static::deleted($queue);

        if (method_exists(static::class, 'restored')) {
            static::restored($queue);
        }
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    abstract public function revalidationTags(): array;
}

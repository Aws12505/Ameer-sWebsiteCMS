<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Collects the cache tags touched during a request and tells the
 * front end to refresh them once, after the response is sent.
 */
class FrontendRevalidator
{
    /**
     * @var array<string, true>
     */
    protected array $pendingTags = [];

    /**
     * Queue cache tags to be refreshed at the end of the request.
     *
     * @param  list<string>  $tags
     */
    public function queue(array $tags): void
    {
        foreach ($tags as $tag) {
            $this->pendingTags[$tag] = true;
        }
    }

    /**
     * The tags waiting to be sent.
     *
     * @return list<string>
     */
    public function pending(): array
    {
        return array_keys($this->pendingTags);
    }

    /**
     * Send the queued tags to the front end's revalidate route.
     */
    public function flush(): void
    {
        $tags = $this->pending();
        $this->pendingTags = [];

        $url = config('cms.frontend.url');
        $secret = config('cms.frontend.revalidate_secret');

        if ($tags === [] || blank($url) || blank($secret)) {
            return;
        }

        try {
            Http::timeout(5)
                ->withHeaders(['x-revalidate-secret' => $secret])
                ->post(rtrim($url, '/').'/api/revalidate', ['tags' => $tags])
                ->throw();
        } catch (Throwable $exception) {
            Log::warning('Front-end revalidation failed.', [
                'tags' => $tags,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

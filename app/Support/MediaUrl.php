<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /**
     * Build the absolute public URL of a stored media file.
     */
    public static function for(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk(config('cms.disk'))->url($path);
    }

    /**
     * Build the `{ src, alt }` image shape used by the public API.
     *
     * @return array{src: string, alt: string}|null
     */
    public static function image(?string $path, ?string $alt): ?array
    {
        $url = self::for($path);

        return $url === null ? null : ['src' => $url, 'alt' => (string) $alt];
    }

    /**
     * Build the `{ path, url, alt }` image shape used by the admin forms.
     *
     * @return array{path: string, url: string, alt: string}|null
     */
    public static function field(?string $path, ?string $alt = null): ?array
    {
        $url = self::for($path);

        return $url === null ? null : ['path' => $path, 'url' => $url, 'alt' => (string) $alt];
    }

    /**
     * Map a stored list of `{ path, alt }` items to the public image shape.
     *
     * @param  array<int, array{path?: string, alt?: string}>|null  $items
     * @return array<int, array{src: string, alt: string}>
     */
    public static function images(?array $items): array
    {
        return collect($items ?? [])
            ->map(fn (array $item): ?array => self::image($item['path'] ?? null, $item['alt'] ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Keep only the stored `{ path, alt }` of validated photos (drops the preview URL).
     *
     * @param  array<int, array{path: string, alt: string}>  $photos
     * @return list<array{path: string, alt: string}>
     */
    public static function stored(array $photos): array
    {
        return array_values(array_map(
            fn (array $photo): array => ['path' => $photo['path'], 'alt' => $photo['alt']],
            $photos,
        ));
    }

    /**
     * Map a stored list of `{ path, alt }` items to the admin field shape.
     *
     * @param  array<int, array{path?: string, alt?: string}>|null  $items
     * @return array<int, array{path: string, url: string, alt: string}>
     */
    public static function fields(?array $items): array
    {
        return collect($items ?? [])
            ->map(fn (array $item): ?array => self::field($item['path'] ?? null, $item['alt'] ?? ''))
            ->filter()
            ->values()
            ->all();
    }
}

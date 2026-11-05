<?php

namespace App\Support;

use App\Enums\HeroMode;
use App\Models\Setting;

/**
 * Turns the stored one-record content into the public API shapes
 * (requirements section 10): image paths become `{ src, alt }`.
 */
class PublicContent
{
    /**
     * The favicon sizes generated from the uploaded 512 px PNG.
     */
    public const array FaviconSizes = [32, 180, 192, 512];

    /**
     * `GET /site`. The notification emails are private and never returned.
     *
     * @return array<string, mixed>
     */
    public static function site(): array
    {
        $site = Setting::valueOf(Setting::Site);
        $favicon = $site['favicon'] ?? null;

        return [
            'name' => $site['name'],
            'description' => $site['description'],
            'serviceArea' => $site['serviceArea'],
            'phone' => [
                'display' => $site['phone']['display'],
                'href' => PhoneNumber::href($site['phone']['display']),
            ],
            'email' => $site['email'],
            'favicon' => $favicon === null ? null : [
                'src' => MediaUrl::for($favicon['path']),
                'svg' => null,
                'sizes' => array_map(MediaUrl::for(...), $favicon['sizes'] ?? []),
            ],
            'cta' => ['label' => $site['cta']['label']],
            'footerBlurb' => $site['footerBlurb'],
            'ctaBand' => [
                'heading' => $site['ctaBand']['heading'],
                'text' => $site['ctaBand']['text'],
            ],
            'defaultShareImage' => self::image($site['defaultShareImage'] ?? null),
        ];
    }

    /**
     * `GET /settings`: hero background and the Reviews switch.
     *
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        $hero = Setting::valueOf(Setting::Hero);
        $video = $hero['heroVideo'] ?? null;

        return [
            'showReviews' => (bool) ($hero['showReviews'] ?? false),
            'heroMode' => $hero['heroMode'] ?? HeroMode::Slider->value,
            'heroSlides' => MediaUrl::images($hero['heroSlides'] ?? []),
            'heroVideo' => blank($video['path'] ?? null) ? null : [
                'src' => MediaUrl::for($video['path']),
                'poster' => MediaUrl::for($video['poster'] ?? null),
            ],
        ];
    }

    /**
     * `GET /pages/home`.
     *
     * @return array<string, mixed>
     */
    public static function homePage(): array
    {
        $page = Setting::valueOf(Setting::HomePage);

        $page['hero']['headline'] = array_values(array_filter($page['hero']['headline'] ?? [], 'filled'));
        $page['about']['image'] = self::image($page['about']['image'] ?? null);
        $page['seo'] = self::seo($page['seo'] ?? []);

        return $page;
    }

    /**
     * `GET /pages/services`, `GET /pages/projects` and `GET /pages/contact`.
     *
     * @return array<string, mixed>
     */
    public static function page(string $key): array
    {
        $page = Setting::valueOf($key);
        $page['seo'] = self::seo($page['seo'] ?? []);

        return $page;
    }

    /**
     * The `{ title, description, image }` SEO shape, empty strings meaning "use the default".
     *
     * @param  array<string, mixed>  $seo
     * @return array{title: string, description: string, image: array{src: string, alt: string}|null}
     */
    public static function seo(array $seo): array
    {
        return [
            'title' => (string) ($seo['title'] ?? ''),
            'description' => (string) ($seo['description'] ?? ''),
            'image' => self::image($seo['image'] ?? null),
        ];
    }

    /**
     * A stored `{ path, alt }` image as `{ src, alt }`.
     *
     * @param  array{path?: string|null, alt?: string|null}|null  $image
     * @return array{src: string, alt: string}|null
     */
    public static function image(?array $image): ?array
    {
        return $image === null ? null : MediaUrl::image($image['path'] ?? null, $image['alt'] ?? '');
    }
}

<?php

namespace App\Support;

use App\Enums\HeroMode;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Loads API-shaped content (the front end's src/lib/mock/data.ts exported as
 * JSON) into the CMS. Media `src` values may be URLs, which are downloaded,
 * or paths relative to the JSON file, which are copied. Running it again
 * updates the same records instead of adding new ones.
 */
class ContentImporter
{
    /**
     * Media sources already stored during this run, mapped to their paths.
     *
     * @var array<string, string>
     */
    protected array $stored = [];

    /**
     * The folder that relative media paths are read from.
     */
    protected string $baseDirectory = '';

    public function __construct(protected FaviconGenerator $favicons) {}

    /**
     * Import a content file and return how many items of each kind it held.
     *
     * @return array<string, int>
     */
    public function importFile(string $file, bool $published = true): array
    {
        if (! is_file($file)) {
            throw new RuntimeException("File not found: {$file}");
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (! is_array($data)) {
            throw new RuntimeException("{$file} is not valid JSON.");
        }

        $this->baseDirectory = dirname((string) realpath($file));

        return DB::transaction(fn (): array => $this->import($data, $published));
    }

    /**
     * Import already-decoded content.
     *
     * @param  array<mixed>  $data
     * @return array<string, int>
     */
    public function import(array $data, bool $published = true): array
    {
        return array_filter([
            'Core services' => $this->importServiceCategories($this->items($data['serviceCategories'] ?? null), $this->items($data['serviceFilters'] ?? null), $published),
            'Services' => $this->importServices($this->items($data['services'] ?? null), $published),
            'Project categories' => $this->importProjectCategories($this->items($data['projectCategories'] ?? null), $published),
            'Projects' => $this->importProjects($this->items($data['projects'] ?? null), $published),
            'Reviews' => $this->importReviews($this->items($data['reviews'] ?? null), $published),
            'Hero slides' => $this->importHero(is_array($data['settings'] ?? null) ? $data['settings'] : null),
            'About photo' => $this->importAboutImage($this->photo($data['aboutImage'] ?? null)),
            'Favicon' => $this->importFavicon(is_string($data['favicon'] ?? null) ? $data['favicon'] : null),
        ], fn (?int $count): bool => $count !== null);
    }

    /**
     * @param  list<array<mixed>>  $items
     * @param  list<array<mixed>>  $filters
     */
    protected function importServiceCategories(array $items, array $filters, bool $published): ?int
    {
        if ($items === []) {
            return null;
        }

        $labels = array_column($filters, 'label', 'key');

        foreach ($items as $position => $item) {
            $key = (string) $item['key'];
            $category = ServiceCategory::withTrashed()->firstOrNew(['slug' => $key]);
            $category->fill([
                'title' => $item['title'],
                'filter_label' => Str::limit($labels[$key] ?? (string) $item['title'], 20, ''),
                'summary' => $item['summary'],
                'image_path' => $this->store($this->photo($item['image'] ?? null)),
                'image_alt' => $this->photo($item['image'] ?? null)['alt'] ?? $item['title'],
                'body' => $this->strings($item['body'] ?? null),
                'gallery' => $this->gallery($this->photos($item['gallery'] ?? null)),
                'external_url' => $item['externalUrl'] ?? null,
                'show_on_home' => $item['showOnHome'] ?? true,
                'is_pinned' => $position === 0 && filled($item['externalUrl'] ?? null),
                'published' => $published,
            ]);
            $category->sort_order = $position + 1;
            $category->deleted_at = null;
            $category->save();
        }

        return count($items);
    }

    /**
     * @param  list<array<mixed>>  $items
     */
    protected function importServices(array $items, bool $published): ?int
    {
        if ($items === []) {
            return null;
        }

        $categoryIds = ServiceCategory::query()->pluck('id', 'slug');

        foreach ($items as $position => $item) {
            $service = Service::withTrashed()->firstOrNew(['slug' => $item['id']]);
            $service->fill([
                'title' => $item['title'],
                'description' => $item['description'],
                'includes' => $this->strings($item['includes'] ?? null),
                'image_path' => $this->store($this->photo($item['image'] ?? null)),
                'image_alt' => $this->photo($item['image'] ?? null)['alt'] ?? $item['title'],
                'published' => $published,
            ]);
            $service->sort_order = $position + 1;
            $service->deleted_at = null;
            $service->save();

            $service->categories()->sync($this->idsFor($this->strings($item['categories'] ?? null), $categoryIds->all()));
        }

        return count($items);
    }

    /**
     * @param  list<array<mixed>>  $items
     */
    protected function importProjectCategories(array $items, bool $published): ?int
    {
        if ($items === []) {
            return null;
        }

        foreach ($items as $position => $item) {
            $category = ProjectCategory::withTrashed()->firstOrNew(['slug' => $item['key']]);
            $category->fill(['label' => $item['label'], 'published' => $published]);
            $category->sort_order = $position + 1;
            $category->deleted_at = null;
            $category->save();
        }

        return count($items);
    }

    /**
     * Projects keep the file's order. The cover is left out of the stored
     * gallery because the API always adds it back as the first photo.
     *
     * @param  list<array<mixed>>  $items
     */
    protected function importProjects(array $items, bool $published): ?int
    {
        if ($items === []) {
            return null;
        }

        $categoryIds = ProjectCategory::query()->pluck('id', 'slug')->all();

        foreach ($items as $position => $item) {
            $keys = $this->strings($item['categories'] ?? null);
            $cover = $this->photo($item['cover'] ?? null);
            $gallery = array_values(array_filter(
                $this->photos($item['gallery'] ?? null),
                fn (array $photo): bool => $photo['src'] !== ($cover['src'] ?? null),
            ));

            $project = Project::withTrashed()->firstOrNew(['slug' => $item['id']]);
            $project->fill([
                'title' => $item['title'],
                'location' => $item['location'],
                'description' => $item['description'],
                'main_category_id' => $categoryIds[$keys[0] ?? ''] ?? null,
                'featured' => (bool) ($item['featured'] ?? false),
                'cover_path' => $this->store($cover),
                'cover_alt' => $cover['alt'] ?? $item['title'],
                'gallery' => $this->gallery($gallery),
                'published' => $published,
            ]);
            $project->sort_order = $position + 1;
            $project->deleted_at = null;
            $project->save();

            $project->extraCategories()->sync($this->idsFor(array_slice($keys, 1), $categoryIds));
        }

        return count($items);
    }

    /**
     * @param  list<array<mixed>>  $items
     */
    protected function importReviews(array $items, bool $published): ?int
    {
        if ($items === []) {
            return null;
        }

        foreach ($items as $position => $item) {
            $review = Review::withTrashed()->firstOrNew(['slug' => $item['id']]);
            $review->fill([
                'name' => $item['name'],
                'project' => $item['project'],
                'rating' => (int) $item['rating'],
                'quote' => $item['quote'],
                'photo_path' => is_string($item['photo'] ?? null) ? $this->store(['src' => $item['photo'], 'alt' => '']) : null,
                'published' => $published,
            ]);
            $review->sort_order = $position + 1;
            $review->deleted_at = null;
            $review->save();
        }

        return count($items);
    }

    /**
     * The hero background and the Reviews switch.
     *
     * @param  array<mixed>|null  $settings
     */
    protected function importHero(?array $settings): ?int
    {
        if ($settings === null) {
            return null;
        }

        $slides = $this->gallery($this->photos($settings['heroSlides'] ?? null));
        $video = is_array($settings['heroVideo'] ?? null) ? $settings['heroVideo'] : null;
        $hero = Setting::for(Setting::Hero);

        $hero->update(['value' => [
            ...$hero->value,
            'showReviews' => (bool) ($settings['showReviews'] ?? false),
            'heroMode' => HeroMode::tryFrom((string) ($settings['heroMode'] ?? ''))->value
                ?? (count($slides) > 1 ? HeroMode::Slider->value : HeroMode::Image->value),
            'heroSlides' => $slides,
            'heroVideo' => $video === null ? null : [
                'path' => $this->store(['src' => (string) $video['src']]),
                'poster' => $this->store(['src' => (string) $video['poster']]),
            ],
        ]]);

        return count($slides);
    }

    /**
     * @param  array{src: string, alt: string}|null  $image
     */
    protected function importAboutImage(?array $image): ?int
    {
        if ($image === null) {
            return null;
        }

        $setting = Setting::for(Setting::HomePage);
        $page = $setting->value;
        $page['about']['image'] = ['path' => $this->store($image), 'alt' => $image['alt']];
        $setting->update(['value' => $page]);

        return 1;
    }

    protected function importFavicon(?string $src): ?int
    {
        if ($src === null) {
            return null;
        }

        $path = (string) $this->store(['src' => $src]);
        $setting = Setting::for(Setting::Site);
        $setting->update(['value' => [
            ...$setting->value,
            'favicon' => ['path' => $path, 'sizes' => $this->favicons->generate($path)],
        ]]);

        return 1;
    }

    /**
     * @param  list<array{src: string, alt: string}>  $photos
     * @return list<array{path: string, alt: string}>
     */
    protected function gallery(array $photos): array
    {
        $stored = [];

        foreach ($photos as $photo) {
            $path = $this->store($photo);

            if ($path !== null) {
                $stored[] = ['path' => $path, 'alt' => $photo['alt']];
            }
        }

        return $stored;
    }

    /**
     * Copy (or download) one media file into the media disk and return its path.
     * The same source always lands at the same path, so imports can be repeated.
     *
     * @param  array{src: string, alt?: string}|null  $media
     */
    protected function store(?array $media): ?string
    {
        $src = $media['src'] ?? null;

        if (blank($src)) {
            return null;
        }

        if (isset($this->stored[$src])) {
            return $this->stored[$src];
        }

        $isUrl = Str::startsWith($src, ['http://', 'https://']);
        $contents = $isUrl ? Http::timeout(60)->get($src)->throw()->body() : $this->readLocal($src);
        $extension = strtolower(pathinfo((string) parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        $name = $isUrl ? sha1($src).'.'.$extension : basename($src);

        $path = 'content/'.$name;
        $disk = Storage::disk(config('cms.disk'));

        if (! $disk->exists($path)) {
            $disk->put($path, $contents, 'public');
        }

        return $this->stored[$src] = $path;
    }

    /**
     * Read a media file that sits next to the content file.
     */
    protected function readLocal(string $relativePath): string
    {
        $base = (string) realpath($this->baseDirectory);
        $path = realpath($base.DIRECTORY_SEPARATOR.$relativePath);

        if ($path === false || ! str_starts_with($path, $base) || ! is_file($path)) {
            throw new RuntimeException("Media file not found next to the content file: {$relativePath}");
        }

        return (string) file_get_contents($path);
    }

    /**
     * The ids for the given keys, skipping unknown keys.
     *
     * @param  list<string>  $keys
     * @param  array<string, int>  $idsByKey
     * @return list<int>
     */
    protected function idsFor(array $keys, array $idsByKey): array
    {
        return array_values(array_filter(array_map(fn (string $key): ?int => $idsByKey[$key] ?? null, $keys)));
    }

    /**
     * The objects in a JSON list.
     *
     * @return list<array<mixed>>
     */
    protected function items(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, 'is_array')) : [];
    }

    /**
     * The strings in a JSON list.
     *
     * @return list<string>
     */
    protected function strings(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    /**
     * A `{ src, alt }` photo, or null.
     *
     * @return array{src: string, alt: string}|null
     */
    protected function photo(mixed $value): ?array
    {
        if (! is_array($value) || ! is_string($value['src'] ?? null)) {
            return null;
        }

        return ['src' => $value['src'], 'alt' => is_string($value['alt'] ?? null) ? $value['alt'] : ''];
    }

    /**
     * The `{ src, alt }` photos in a JSON list.
     *
     * @return list<array{src: string, alt: string}>
     */
    protected function photos(mixed $value): array
    {
        return array_values(array_filter(array_map($this->photo(...), is_array($value) ? $value : [])));
    }
}

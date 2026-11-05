<?php

namespace App\Models;

use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use App\Support\DefaultContent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One-record content: site settings, hero settings and the page texts.
 *
 * @property int $id
 * @property string $key
 * @property array<string, mixed> $value
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    use RevalidatesFrontend, TracksEditor;

    public const string Site = 'site';

    public const string Hero = 'hero';

    public const string HomePage = 'page.home';

    public const string ServicesPage = 'page.services';

    public const string ProjectsPage = 'page.projects';

    public const string ContactPage = 'page.contact';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Find the record for a key, creating it from the starting content if needed.
     */
    public static function for(string $key): self
    {
        return static::query()->with('editor')->firstOrCreate(
            ['key' => $key],
            ['value' => DefaultContent::for($key)],
        );
    }

    /**
     * The stored value for a key.
     *
     * @return array<string, mixed>
     */
    public static function valueOf(string $key): array
    {
        return static::for($key)->value;
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    public function revalidationTags(): array
    {
        return match (true) {
            $this->key === self::Site => config('cms.frontend.tags'),
            $this->key === self::Hero => ['settings'],
            default => ['pages'],
        };
    }
}

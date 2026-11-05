<?php

namespace App\Models;

use App\Concerns\HasPublishing;
use App\Concerns\IsSortable;
use App\Concerns\PrunesTrash;
use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use Carbon\CarbonImmutable;
use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A core service: home card, footer link, filter chip and its own page.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $filter_label
 * @property string $summary
 * @property string|null $image_path
 * @property string|null $image_alt
 * @property list<string>|null $body
 * @property list<array{path: string, alt: string}>|null $gallery
 * @property string|null $external_url
 * @property bool $show_on_home
 * @property bool $is_pinned
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_image_path
 * @property string|null $seo_image_alt
 * @property bool $published
 * @property CarbonImmutable|null $first_published_at
 * @property int $sort_order
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable([
    'slug', 'title', 'filter_label', 'summary', 'image_path', 'image_alt', 'body', 'gallery',
    'external_url', 'show_on_home', 'is_pinned', 'seo_title', 'seo_description', 'seo_image_path',
    'seo_image_alt', 'published',
])]
class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory, HasPublishing, IsSortable, PrunesTrash, RevalidatesFrontend, TracksEditor;

    /**
     * The most core services that can show as cards on the home page.
     */
    public const int MaxOnHome = 4;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'gallery' => 'array',
            'show_on_home' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * The detailed services that belong to this core service.
     *
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Pinned records first (New Construction), then the manager's order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderByDesc('is_pinned')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Whether another company handles this service (no page on this site).
     */
    public function isExternal(): bool
    {
        return filled($this->external_url);
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    public function revalidationTags(): array
    {
        return ['services'];
    }
}

<?php

namespace App\Models;

use App\Concerns\HasPublishing;
use App\Concerns\IsSortable;
use App\Concerns\PrunesTrash;
use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use Carbon\CarbonImmutable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A finished project, shown as a card that opens a photo gallery.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $location
 * @property string $description
 * @property int|null $main_category_id
 * @property bool $featured
 * @property string|null $cover_path
 * @property string|null $cover_alt
 * @property list<array{path: string, alt: string}>|null $gallery
 * @property bool $published
 * @property CarbonImmutable|null $first_published_at
 * @property int $sort_order
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable([
    'slug', 'title', 'location', 'description', 'main_category_id', 'featured', 'cover_path', 'cover_alt',
    'gallery', 'published',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasPublishing, IsSortable, PrunesTrash, RevalidatesFrontend, TracksEditor;

    /**
     * The most projects that can be featured on the home page.
     */
    public const int MaxFeatured = 3;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'gallery' => 'array',
        ];
    }

    /**
     * The main category, shown on the card.
     *
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'main_category_id');
    }

    /**
     * Other categories the project is listed under.
     *
     * @return BelongsToMany<ProjectCategory, $this>
     */
    public function extraCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProjectCategory::class);
    }

    /**
     * New projects go to the top of the list (newest first).
     */
    public function addsNewItemsFirst(): bool
    {
        return true;
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    public function revalidationTags(): array
    {
        return ['projects'];
    }
}

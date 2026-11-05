<?php

namespace App\Models;

use App\Concerns\HasPublishing;
use App\Concerns\IsSortable;
use App\Concerns\PrunesTrash;
use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use Carbon\CarbonImmutable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A detailed service, listed on the Services page and its core service pages.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property list<string> $includes
 * @property string|null $image_path
 * @property string|null $image_alt
 * @property bool $published
 * @property CarbonImmutable|null $first_published_at
 * @property int $sort_order
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable(['slug', 'title', 'description', 'includes', 'image_path', 'image_alt', 'published'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasPublishing, IsSortable, PrunesTrash, RevalidatesFrontend, TracksEditor;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'includes' => 'array',
        ];
    }

    /**
     * The core services this service belongs to.
     *
     * @return BelongsToMany<ServiceCategory, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class);
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

<?php

namespace App\Models;

use App\Concerns\HasPublishing;
use App\Concerns\IsSortable;
use App\Concerns\PrunesTrash;
use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use Carbon\CarbonImmutable;
use Database\Factories\ProjectCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A filter chip on the Projects page and the label on project cards.
 *
 * @property int $id
 * @property string $slug
 * @property string $label
 * @property bool $published
 * @property CarbonImmutable|null $first_published_at
 * @property int $sort_order
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable(['slug', 'label', 'published'])]
class ProjectCategory extends Model
{
    /** @use HasFactory<ProjectCategoryFactory> */
    use HasFactory, HasPublishing, IsSortable, PrunesTrash, RevalidatesFrontend, TracksEditor;

    /**
     * The projects that use this category, as main or extra category.
     *
     * @return Builder<Project>
     */
    public function projectsUsingIt(): Builder
    {
        return Project::query()->where(fn (Builder $query) => $query
            ->where('main_category_id', $this->id)
            ->orWhereHas('extraCategories', fn (Builder $query) => $query->whereKey($this->id)));
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

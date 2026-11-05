<?php

namespace App\Models;

use App\Concerns\HasPublishing;
use App\Concerns\IsSortable;
use App\Concerns\PrunesTrash;
use App\Concerns\RevalidatesFrontend;
use App\Concerns\TracksEditor;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A customer review shown on the home page.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property-read string $initials
 * @property string $project
 * @property int $rating
 * @property string $quote
 * @property string|null $photo_path
 * @property bool $published
 * @property CarbonImmutable|null $first_published_at
 * @property int $sort_order
 * @property int|null $updated_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable(['slug', 'name', 'project', 'rating', 'quote', 'photo_path', 'published'])]
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, HasPublishing, IsSortable, PrunesTrash, RevalidatesFrontend, TracksEditor;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * Up to three initials built from the name, shown when there is no photo.
     *
     * @return Attribute<string, never>
     */
    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(3)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode(''));
    }

    /**
     * The front-end cache tags that show this record.
     *
     * @return list<string>
     */
    public function revalidationTags(): array
    {
        return ['reviews'];
    }
}

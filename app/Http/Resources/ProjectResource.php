<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * The main category comes first in `categories`, and the cover is
     * always the first gallery photo, so the manager uploads it once.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cover = MediaUrl::image($this->cover_path, $this->cover_alt);

        $extraCategories = $this->extraCategories
            ->reject(fn (ProjectCategory $category): bool => $category->id === $this->main_category_id)
            ->sortBy('sort_order');

        return [
            'id' => $this->slug,
            'title' => $this->title,
            'location' => $this->location,
            'description' => $this->description,
            'categories' => collect([$this->mainCategory])
                ->concat($extraCategories)
                ->filter(fn (?ProjectCategory $category): bool => $category?->published === true)
                ->map(fn (ProjectCategory $category): string => $category->slug)
                ->values()
                ->all(),
            'featured' => $this->featured,
            'cover' => $cover,
            'gallery' => array_values(array_filter([$cover, ...MediaUrl::images($this->gallery)])),
        ];
    }
}

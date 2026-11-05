<?php

namespace App\Http\Resources;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'categories' => $this->categories
                ->sortBy([['is_pinned', 'desc'], ['sort_order', 'asc']])
                ->map(fn (ServiceCategory $category): string => $category->slug)
                ->values()
                ->all(),
            'includes' => $this->includes,
            'image' => MediaUrl::image($this->image_path, $this->image_alt),
        ];
    }
}

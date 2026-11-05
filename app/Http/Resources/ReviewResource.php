<?php

namespace App\Http\Resources;

use App\Models\Review;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
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
            'name' => $this->name,
            'initials' => $this->initials,
            'project' => $this->project,
            'rating' => $this->rating,
            'quote' => $this->quote,
            ...(filled($this->photo_path) ? ['photo' => MediaUrl::for($this->photo_path)] : []),
        ];
    }
}

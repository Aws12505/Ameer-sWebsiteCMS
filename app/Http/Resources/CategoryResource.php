<?php

namespace App\Http\Resources;

use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A project category as a `{ key, label }` filter chip.
 *
 * @mixin ProjectCategory
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{key: string, label: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->slug,
            'label' => $this->label,
        ];
    }
}

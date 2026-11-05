<?php

namespace App\Http\Resources;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A core service as a `{ key, label }` Services page filter chip.
 *
 * @mixin ServiceCategory
 */
class ServiceFilterResource extends JsonResource
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
            'label' => $this->filter_label,
        ];
    }
}

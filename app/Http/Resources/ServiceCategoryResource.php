<?php

namespace App\Http\Resources;

use App\Models\ServiceCategory;
use App\Support\MediaUrl;
use App\Support\PublicContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ServiceCategory
 */
class ServiceCategoryResource extends JsonResource
{
    /**
     * Include the page-only fields (`body`, `gallery`, `seo`).
     */
    protected bool $withDetail = false;

    /**
     * Return the full record used by `/service-categories/{key}`.
     */
    public function withDetail(): static
    {
        $this->withDetail = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->slug,
            'title' => $this->title,
            'filterLabel' => $this->filter_label,
            'summary' => $this->summary,
            'externalUrl' => $this->external_url,
            'showOnHome' => $this->show_on_home,
            'image' => MediaUrl::image($this->image_path, $this->image_alt),
            ...($this->withDetail ? [
                'body' => $this->isExternal() ? [] : ($this->body ?? []),
                'gallery' => $this->isExternal() ? [] : MediaUrl::images($this->gallery),
                'seo' => PublicContent::seo([
                    'title' => $this->seo_title,
                    'description' => $this->seo_description,
                    'image' => $this->seo_image_path ? ['path' => $this->seo_image_path, 'alt' => $this->seo_image_alt] : null,
                ]),
            ] : []),
        ];
    }
}

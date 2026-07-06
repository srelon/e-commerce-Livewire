<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'image' => $this->image['original'] ?? null,
            'seo' => [
                'title' => $this->resource->seo?->seo_title ?? $this->title,
                'description' => $this->resource->seo?->seo_description ?? $this->excerpt,
                'keywords' => $this->resource->seo?->seo_keywords,
            ],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function __construct($resource, protected bool $detailed = false) {
        parent::__construct($resource);
    }

    public function toArray($request): array {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($this->detailed, fn () => $this->content),
            'author' => $this->author?->name,
            'date' => $this->published_at?->toDateString(),
            'image' => $this->image['original'] ?? null,
            'category' => $this->category?->name,
            'seo' => $this->when($this->detailed, fn () => [
                'title' => $this->resource->seo?->seo_title ?? $this->title,
                'description' => $this->resource->seo?->seo_description ?? $this->excerpt,
                'keywords' => $this->resource->seo?->seo_keywords,
            ]),
        ];
    }
}

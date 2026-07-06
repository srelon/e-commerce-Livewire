<?php

namespace App\Http\Resources;

use App\Models\ProductImage;
use App\Models\ProductStock;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function __construct($resource, protected bool $detailed = false, protected bool $includeStock = true) {
        parent::__construct($resource);
    }

    public function toArray($request): array {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'author' => $this->author?->name,
            'category' => $this->category?->name,
            'rating' => $this->rating_avg !== null ? (float) $this->rating_avg : null,
            'image' => $this->when(! $this->detailed, fn () => $this->primaryImage?->image['original'] ?? null),
            'reviews_count' => $this->when($this->detailed, fn () => $this->reviews_count),
            'short_description' => $this->when($this->detailed, fn () => $this->short_description),
            'description' => $this->when($this->detailed, fn () => $this->description),
            'images' => $this->when($this->detailed, fn () => $this->resource->images
                ->map(fn (ProductImage $image) => $image->image['original'] ?? null)
                ->filter()
                ->values()
                ->all()),
            'stock' => $this->formatStock($this->activeStock),
            'seo' => $this->when($this->detailed, fn () => [
                'title' => $this->resource->seo?->seo_title ?? $this->title,
                'description' => $this->resource->seo?->seo_description ?? $this->short_description,
                'keywords' => $this->resource->seo?->seo_keywords,
            ]),
        ];
    }

    protected function formatStock(?ProductStock $stock): array {
        $data = [
            'price' => $stock?->price,
            'before_price' => $stock?->before_price,
        ];

        if ($this->includeStock) {
            $data['id'] = $stock?->id;
            $data['quantity'] = $this->availableStockQuantity($stock);
        }

        return $data;
    }

    protected function availableStockQuantity(?ProductStock $stock): int {
        if (! $stock) {
            return 0;
        }

        $reserved = ($stock->pending_quantity ?? 0) + ($stock->fulfilled_quantity ?? 0);

        return max(0, $stock->quantity - $reserved);
    }
}

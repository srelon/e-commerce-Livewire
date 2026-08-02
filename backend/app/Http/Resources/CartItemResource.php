<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array {
        $product = $this->resource->product;

        return [
            'slug' => $product->slug,
            'title' => $product->title,
            'author' => $product->author?->name,
            'image' => $product->primaryImage?->image['original'] ?? null,
            'price' => $product->activeStock?->price,
            'quantity' => $this->quantity,
            'available' => $product->activeStock?->availableQuantity() ?? 0,
        ];
    }
}

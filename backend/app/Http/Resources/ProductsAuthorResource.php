<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductsAuthorResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'initials' => $this->initials(),
            'color' => $this->avatar_color ?? '#999999',
            'bio' => $this->content ?? '',
            'books' => $this->products_count,
            'bestsellers' => (int) $this->bestseller_sum,
        ];
    }

    protected function initials(): string {
        $parts = preg_split('/\s+/', trim($this->name));

        return strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    }
}

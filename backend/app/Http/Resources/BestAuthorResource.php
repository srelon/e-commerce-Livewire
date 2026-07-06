<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BestAuthorResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'content' => $this->content,
            'photo' => $this->photo['original'] ?? null,
        ];
    }
}

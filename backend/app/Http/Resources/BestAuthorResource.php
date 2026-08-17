<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BestAuthorResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'name' => $this->name,
            'content' => $this->content,
            'photo' => $this->photo['original'] ?? null,
        ];
    }
}

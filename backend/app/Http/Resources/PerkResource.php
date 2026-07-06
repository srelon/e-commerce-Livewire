<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PerkResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'title' => $this->title,
            'desc' => $this->description,
            'icon' => $this->icon,
        ];
    }
}

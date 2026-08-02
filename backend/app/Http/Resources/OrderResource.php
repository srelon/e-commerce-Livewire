<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'public_id' => $this->public_id,
            'status' => $this->status,
        ];
    }
}

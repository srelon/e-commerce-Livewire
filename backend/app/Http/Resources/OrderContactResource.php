<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderContactResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'delivery_id' => $this->delivery_id,
            'branch_id' => $this->delivery_branch_id,
        ];
    }
}

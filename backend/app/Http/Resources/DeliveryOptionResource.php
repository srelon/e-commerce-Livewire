<?php

namespace App\Http\Resources;

use App\Models\DeliveryBranch;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOptionResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'requires_branch' => $this->resource->branches->isNotEmpty(),
            'branches' => $this->resource->branches
                ->map(fn (DeliveryBranch $branch) => [
                    'id' => $branch->id,
                    'city' => $branch->city,
                    'branch' => $branch->branch,
                ])
                ->values()
                ->all(),
        ];
    }
}

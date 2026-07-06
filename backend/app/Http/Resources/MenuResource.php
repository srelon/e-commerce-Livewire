<?php

namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'route' => $this->route,
            'params' => $this->params,
            'children' => $this->resource->children->map(fn (Menu $child) => (new self($child))->resolve($request))->all(),
        ];
    }
}

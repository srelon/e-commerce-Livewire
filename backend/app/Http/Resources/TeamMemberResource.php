<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'initials' => $this->initials,
            'color' => $this->color,
            'bio' => $this->bio,
        ];
    }
}

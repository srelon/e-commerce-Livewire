<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
            'from_user' => $this->fromUser ? [
                'name' => $this->fromUser->name,
                'avatar' => $this->fromUser->avatar,
            ] : null,
            'product' => $this->product ? [
                'slug' => $this->product->slug,
                'title' => $this->product->title,
            ] : null,
            'review_id' => $this->review_id,
            'parent_id' => $this->parent_id,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function __construct($resource, protected ?User $viewer = null) {
        parent::__construct($resource);
    }

    public function toArray($request): array {
        $replies = ($this->resource->parent_id === null && $this->resource->relationLoaded('replies'))
            ? $this->resource->replies->map(fn (Review $reply) => (new self($reply, $this->viewer))->resolve($request))->all()
            : [];

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'replied_to_comment_id' => $this->replied_to_comment_id,
            'replied_to' => $this->resource->repliedTo ? [
                'id' => $this->resource->repliedTo->id,
                'user' => [
                    'name' => $this->resource->repliedTo->user?->name,
                ],
            ] : null,
            'user' => [
                'name' => $this->resource->user?->name,
                'avatar' => $this->resource->user?->avatar,
            ],
            'rating' => $this->rating,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
            'can_edit' => $this->viewer ? $this->resource->canEditBy($this->viewer) : false,
            'can_delete' => $this->viewer ? $this->user_id === $this->viewer->id : false,
            'likes_count' => $this->resource->likesCount(),
            'dislikes_count' => $this->resource->dislikesCount(),
            'my_reaction' => $this->resource->reactionOf($this->viewer),
            'replies_count' => count($replies),
            'replies' => $replies,
        ];
    }
}

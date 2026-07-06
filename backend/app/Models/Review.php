<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'record_id',
        'user_id',
        'parent_id',
        'replied_to_comment_id',
        'rating',
        'body',
        'status',
        'deleted_by',
    ];

    public function reviewable(): MorphTo {
        return $this->morphTo('reviewable', 'type', 'record_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    public function repliedTo(): BelongsTo {
        return $this->belongsTo(Review::class, 'replied_to_comment_id');
    }

    public function replies(): HasMany {
        return $this->hasMany(Review::class, 'parent_id');
    }

    public function likes(): HasMany {
        return $this->hasMany(ReviewLike::class, 'review_id');
    }

    public function reports(): HasMany {
        return $this->hasMany(ReviewReport::class, 'review_id');
    }

    public function canEditBy(User $user): bool {
        return $this->user_id === $user->id && $this->created_at->gt(now()->subDay());
    }

    public function likesCount(): int {
        return $this->likes->where('opp_type', 2)->count();
    }

    public function dislikesCount(): int {
        return $this->likes->where('opp_type', 1)->count();
    }

    public function reactionOf(?User $user): ?string {
        if (! $user) {
            return null;
        }

        $reaction = $this->likes->firstWhere('user_id', $user->id);

        return match ($reaction ? (int) $reaction->opp_type : null) {
            2 => 'like',
            1 => 'dislike',
            default => null,
        };
    }
}

<?php

namespace App\Services;

use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;

class ReviewService
{
    public function __construct(protected NotificationService $notificationService) {}

    public function resolveProduct(string $slug): Product {
        return Product::where('slug', $slug)->firstOrFail();
    }

    public function resolveReview(string $slug, int $reviewId): Review {
        $product = $this->resolveProduct($slug);

        return $product->reviews()->findOrFail($reviewId);
    }

    public function getPaginated(Product $product, ?User $viewer, int $perPage = 10, ?int $pinId = null, bool $excludeViewerOwn = true): LengthAwarePaginator {
        $query = $product->reviews()
            ->whereNull('parent_id')
            ->when($viewer && $excludeViewerOwn, fn ($query) => $query->where('user_id', '!=', $viewer->id))
            ->with([
                'user',
                'likes',
                'replies' => fn ($query) => $query->oldest()->with(['user', 'likes', 'repliedTo.user']),
            ])
            ->when($pinId, fn ($query) => $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$pinId]));

        return $query
            ->latest()
            ->paginate($perPage)
            ->through(fn (Review $review) => $this->formatReview($review, $viewer));
    }

    public function getRatingBreakdown(Product $product): array {
        $counts = $product->reviews()
            ->whereNull('parent_id')
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return collect(range(5, 1))
            ->mapWithKeys(fn (int $star) => [$star => (int) ($counts[$star] ?? 0)])
            ->all();
    }

    public function getViewerReview(Product $product, ?User $viewer): ?array {
        if (! $viewer) {
            return null;
        }

        $review = $product->reviews()
            ->where('user_id', $viewer->id)
            ->whereNull('parent_id')
            ->with([
                'user',
                'likes',
                'replies' => fn ($query) => $query->oldest()->with(['user', 'likes', 'repliedTo.user']),
            ])
            ->first();

        return $review ? $this->formatReview($review, $viewer) : null;
    }

    public function hasReview(Product $product, User $user): bool {
        return $product->reviews()
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->exists();
    }

    public function create(Product $product, User $user, array $data): Review {
        $parent_id = $data['parent_id'] ?? null;
        $replied_to_comment_id = $data['replied_to_comment_id'] ?? null;

        $review = $product->reviews()->create([
            'user_id' => $user->id,
            'parent_id' => $parent_id,
            'replied_to_comment_id' => $replied_to_comment_id,
            'rating' => $parent_id ? null : $data['rating'],
            'body' => $this->sanitizeBody($data['body']),
            'status' => 1,
        ]);

        $this->recalculateRating($product);

        $review->load(['user', 'likes', 'repliedTo.user']);
        $this->publish($product, 'review.created', $this->formatReview($review, $user));

        if ($parent_id) {
            $notify_target = Review::find($replied_to_comment_id ?? $parent_id);

            if ($notify_target && $notify_target->user_id && $notify_target->user_id !== $user->id) {
                $this->notificationService->create(
                    $notify_target->user,
                    'reply',
                    [
                        'from_name' => $user->name,
                        'review_preview' => mb_substr(strip_tags($review->body), 0, 100),
                    ],
                    $product->id,
                    $review->id,
                    $parent_id,
                );
            }
        }

        return $review;
    }

    public function update(Review $review, array $data): Review {
        $review->update([
            'rating' => $review->parent_id ? null : $data['rating'],
            'body' => $this->sanitizeBody($data['body']),
        ]);

        $product = $review->reviewable;
        $this->recalculateRating($product);

        $review->load(['user', 'likes', 'repliedTo.user']);
        $this->publish($product, 'review.updated', $this->formatReview($review, $review->user));

        return $review;
    }

    public function delete(Review $review, ?int $deletedBy = null): void {
        $product = $review->reviewable;
        $review_id = $review->id;
        $parent_id = $review->parent_id;

        if ($deletedBy) {
            $review->deleted_by = $deletedBy;
            $review->save();
        }

        $review->delete();

        $this->recalculateRating($product);

        $this->publish($product, 'review.deleted', ['id' => $review_id, 'parent_id' => $parent_id]);
    }

    public function toggleReaction(Review $review, User $user, string $type): array {
        $opp_type = $type === 'like' ? 2 : 1;

        $like = ReviewLike::firstOrNew([
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);

        $like->opp_type = ((int) $like->opp_type === $opp_type) ? 0 : $opp_type;
        $like->save();

        $review->load('likes');

        $result = [
            'review_id' => $review->id,
            'parent_id' => $review->parent_id,
            'likes_count' => $review->likesCount(),
            'dislikes_count' => $review->dislikesCount(),
            'public_id' => $user->public_id,
            'reaction' => $like->opp_type === 0 ? null : $type,
        ];

        $this->publish($review->reviewable, 'review.liked', $result);

        if ($review->user_id && $review->user_id !== $user->id) {
            if ($like->opp_type === 0) {
                $this->notificationService->deleteReaction($review->user, $review->id, $user->id);
            } else {
                $this->notificationService->upsertReaction(
                    $review->user,
                    $type,
                    ['from_name' => $user->name],
                    $review->reviewable->id,
                    $review->id,
                    $review->parent_id,
                    $user->id,
                );
            }
        }

        return $result;
    }

    public function report(Review $review, User $user, ?string $reason): void {
        ReviewReport::updateOrCreate(
            ['review_id' => $review->id, 'user_id' => $user->id],
            ['reason' => $reason],
        );
    }

    public function formatReview(Review $review, ?User $viewer): array {
        return (new ReviewResource($review, $viewer))->resolve();
    }

    public function sanitizeBody(string $body): string {
        $allowed_tags = '<p><b><i><u><s><strong><em><br>';
        $stripped = strip_tags($body, $allowed_tags);

        return preg_replace('/<(p|b|i|u|s|strong|em|br)\b[^>]*>/i', '<$1>', $stripped);
    }

    protected function recalculateRating(Product $product): void {
        $avg = $product->reviews()->whereNull('parent_id')->avg('rating');

        $product->update([
            'rating_avg' => $avg !== null ? round($avg, 1) : null,
            'reviews_count' => $product->reviews()->whereNull('parent_id')->count(),
        ]);
    }

    protected function publish(Product $product, string $event, array $data): void {
        Redis::publish("reviews.products.{$product->slug}", json_encode([
            'event' => $event,
            'data' => array_merge($data, [
                'product' => [
                    'rating' => $product->rating_avg !== null ? (float) $product->rating_avg : null,
                    'reviews_count' => $product->reviews_count,
                ],
            ]),
        ]));
    }
}

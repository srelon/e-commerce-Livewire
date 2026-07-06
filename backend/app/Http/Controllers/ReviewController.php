<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReactReviewRequest;
use App\Http\Requests\ReportReviewRequest;
use App\Http\Requests\ReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviewService) {}

    public function index(Request $request, string $slug) {
        $product = $this->reviewService->resolveProduct($slug);
        $pinId = $request->query('pin') ? (int) $request->query('pin') : null;
        $paginated = $this->reviewService->getPaginated($product, $request->user(), pinId: $pinId);

        return $this->respondWithJson([
            'items' => $paginated,
            'viewer_review' => $this->reviewService->getViewerReview($product, $request->user()),
            'rating_breakdown' => $this->reviewService->getRatingBreakdown($product),
        ]);
    }

    public function store(ReviewRequest $request, string $slug) {
        $product = $this->reviewService->resolveProduct($slug);
        $user = $request->user();
        $data = $request->validated();

        if (empty($data['parent_id']) && $this->reviewService->hasReview($product, $user)) {
            return $this->respondWithError('You have already reviewed this product.', 403);
        }

        $this->reviewService->create($product, $user, $data);

        return $this->respondWithJson(['message' => 'Review submitted.']);
    }

    public function update(ReviewRequest $request, string $slug, int $review) {
        $review = $this->reviewService->resolveReview($slug, $review);
        $user = $request->user();

        if (! $review->canEditBy($user)) {
            return $this->respondWithError('This review can no longer be edited.', 403);
        }

        $this->reviewService->update($review, $request->validated());

        return $this->respondWithJson(['message' => 'Review updated.']);
    }

    public function destroy(Request $request, string $slug, int $review) {
        $review = $this->reviewService->resolveReview($slug, $review);

        if ($review->user_id !== $request->user()->id) {
            return $this->respondWithError('You can only delete your own review.', 403);
        }

        $this->reviewService->delete($review);

        return $this->respondWithJson(['message' => 'Review deleted.']);
    }

    public function react(ReactReviewRequest $request, string $slug, int $review) {
        $review = $this->reviewService->resolveReview($slug, $review);
        $result = $this->reviewService->toggleReaction($review, $request->user(), $request->validated('type'));

        return $this->respondWithJson($result);
    }

    public function report(ReportReviewRequest $request, string $slug, int $review) {
        $review = $this->reviewService->resolveReview($slug, $review);
        $this->reviewService->report($review, $request->user(), $request->validated('reason'));

        return $this->respondWithJson(['reported' => true]);
    }
}

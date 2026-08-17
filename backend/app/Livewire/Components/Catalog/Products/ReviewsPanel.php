<?php

namespace App\Livewire\Components\Catalog\Products;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasFormHelpers;
use App\Models\Product;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewsPanel extends Component
{
    use ConfirmsAction, HasAccessControl, HasFormHelpers, WithPagination;

    protected string $accessKey = 'reviews';

    public Product $product;

    public $selected_moderator_id = null;

    public ?int $replying_to_id = null;

    public ?int $replying_to_reply_id = null;

    public string $reply_body = '';

    public ?int $editing_id = null;

    public string $editing_body = '';

    public function mount(Product $product): void {
        $this->guardView();
        $this->product = $product;

        $sessionId = (int) session('moderator_user_id');
        $this->selected_moderator_id = $this->moderators->contains('id', $sessionId)
            ? $sessionId
            : $this->moderators->first()?->id;

        if ($this->selected_moderator_id) {
            session(['moderator_user_id' => $this->selected_moderator_id]);
        }
    }

    #[Computed]
    public function reviews(): LengthAwarePaginator {
        return app(ReviewService::class)->getPaginated($this->product, viewer: $this->selectedModerator, perPage: 15, excludeViewerOwn: false);
    }

    #[Computed]
    public function moderators(): Collection {
        return auth('admins')->user()->moderators;
    }

    #[Computed]
    public function selectedModerator(): ?User {
        return $this->selected_moderator_id ? $this->moderators->firstWhere('id', (int) $this->selected_moderator_id) : null;
    }

    public function updatedSelectedModeratorId($value): void {
        $userId = (int) $value;

        if ($userId && $this->moderators->contains('id', $userId)) {
            session(['moderator_user_id' => $userId]);
        } else {
            $this->selected_moderator_id = null;
            session()->forget('moderator_user_id');
        }
    }

    public function like(int $reviewId, string $type): void {
        if (! $this->guardModerator()) {
            return;
        }

        $review = $this->product->reviews()->findOrFail($reviewId);

        app(ReviewService::class)->toggleReaction($review, $this->selectedModerator, $type);

        unset($this->reviews);
    }

    public function startReply(int $parentId, ?int $repliedToId = null): void {
        if (! $this->guardModerator()) {
            return;
        }

        $this->replying_to_id = $parentId;
        $this->replying_to_reply_id = $repliedToId;
        $this->reply_body = '';
    }

    public function cancelReply(): void {
        $this->resetFormFields(['replying_to_id', 'replying_to_reply_id', 'reply_body']);
    }

    public function submitReply(): void {
        if (! $this->guardModerator()) {
            return;
        }

        $this->validate([
            'reply_body' => ['required', 'string', 'max:2000'],
        ]);

        app(ReviewService::class)->create($this->product, $this->selectedModerator, [
            'parent_id' => $this->replying_to_id,
            'replied_to_comment_id' => $this->replying_to_reply_id,
            'body' => $this->reply_body,
        ]);

        $rootId = $this->replying_to_id;

        $this->cancelReply();
        unset($this->reviews);
        $this->dispatch('review-reply-posted', reviewId: $rootId);
        $this->dispatch('notify', type: 'success', message: 'Reply posted.');
    }

    public function startEdit(int $reviewId, string $body): void {
        if (! $this->hasAccess('edit')) {
            return;
        }

        $this->editing_id = $reviewId;
        $this->editing_body = $body;
    }

    public function cancelEdit(): void {
        $this->resetFormFields(['editing_id', 'editing_body']);
    }

    public function submitEdit(): void {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'editing_body' => ['required', 'string', 'max:2000'],
        ]);

        $review = $this->product->reviews()->findOrFail($this->editing_id);

        app(ReviewService::class)->update($review, [
            'body' => $this->editing_body,
            'rating' => $review->rating,
        ]);

        $this->cancelEdit();
        unset($this->reviews);
        $this->dispatch('notify', type: 'success', message: 'Review updated.');
    }

    #[On('deleteReview')]
    public function deleteReview(int $reviewId): void {
        if (! $this->guardSave()) {
            return;
        }

        $review = $this->product->reviews()->findOrFail($reviewId);

        app(ReviewService::class)->delete($review, deletedBy: 1);

        unset($this->reviews);
        $this->dispatch('notify', type: 'success', message: 'Review deleted.');
    }

    public function handleWsReviewEvent(): void {
        unset($this->reviews);
    }

    protected function guardModerator(): bool {
        if (! $this->selectedModerator) {
            $this->dispatch('notify', type: 'danger', message: 'Select a moderator account first.');

            return false;
        }

        return true;
    }

    public function render() {
        return view('livewire.admin.catalog.products.partials.reviews-panel');
    }
}

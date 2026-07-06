<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_index_returns_successful_response_for_a_guest(): void {
        $product = $this->createProduct();

        $this->getJson("/api/products/{$product->slug}/reviews")->assertSuccessful();
    }

    public function test_index_returns_paginated_reviews(): void {
        $product = $this->createProduct();
        $this->createReview($product, $this->createUser(), ['body' => 'First review.']);
        $this->createReview($product, $this->createUser(), ['body' => 'Second review.']);

        $this->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.items.data');
    }

    public function test_index_pins_the_given_review_id_first_regardless_of_sort(): void {
        $product = $this->createProduct();
        $this->createReview($product, $this->createUser(), ['body' => 'Oldest.']);
        $to_pin = $this->createReview($product, $this->createUser(), ['body' => 'Middle.']);
        $this->createReview($product, $this->createUser(), ['body' => 'Newest.']);

        $this->getJson("/api/products/{$product->slug}/reviews?pin={$to_pin->id}")
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.id', $to_pin->id);
    }

    public function test_index_includes_viewer_review_for_authenticated_user_with_a_review(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $this->createReview($product, $user, ['body' => 'My review body.']);

        $this->actingAs($user, 'web')
            ->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonPath('data.viewer_review.body', 'My review body.');
    }

    public function test_index_viewer_review_is_null_for_guest(): void {
        $product = $this->createProduct();
        $this->createReview($product, $this->createUser());

        $this->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonPath('data.viewer_review', null);
    }

    public function test_index_excludes_the_current_users_own_review_from_the_list(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $this->createReview($product, $user, ['body' => 'Mine.']);
        $this->createReview($product, $this->createUser(), ['body' => 'Someone elses.']);

        $this->actingAs($user, 'web')
            ->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items.data')
            ->assertJsonPath('data.items.data.0.body', 'Someone elses.')
            ->assertJsonPath('data.viewer_review.body', 'Mine.');
    }

    public function test_index_returns_rating_breakdown_grouped_by_star(): void {
        $product = $this->createProduct();
        $this->createReview($product, $this->createUser(), ['rating' => 5]);
        $this->createReview($product, $this->createUser(), ['rating' => 5]);
        $this->createReview($product, $this->createUser(), ['rating' => 3]);

        $this->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonPath('data.rating_breakdown.5', 2)
            ->assertJsonPath('data.rating_breakdown.4', 0)
            ->assertJsonPath('data.rating_breakdown.3', 1)
            ->assertJsonPath('data.rating_breakdown.2', 0)
            ->assertJsonPath('data.rating_breakdown.1', 0);
    }

    public function test_index_nests_replies_under_root_reviews_without_counting_them_as_reviews(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser(), ['body' => 'Root review.']);
        $this->createReview($product, $this->createUser(), [
            'parent_id' => $root->id,
            'rating' => null,
            'body' => 'A reply.',
        ]);

        $this->getJson("/api/products/{$product->slug}/reviews")
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items.data')
            ->assertJsonPath('data.items.data.0.replies_count', 1)
            ->assertJsonPath('data.items.data.0.replies.0.body', 'A reply.')
            ->assertJsonPath('data.items.data.0.replies.0.replies_count', 0);
    }

    public function test_replies_do_not_inflate_product_reviews_count(): void {
        $product = $this->createProduct(null, ['reviews_count' => 0]);
        $root = $this->createReview($product, $this->createUser(), ['rating' => 4]);
        $this->createReview($product, $this->createUser(), [
            'parent_id' => $root->id,
            'rating' => null,
        ]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 2,
                'body' => 'Second real review.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'rating_avg' => 3.0,
            'reviews_count' => 2,
        ]);
    }

    public function test_store_creates_a_reply_with_parent_id_and_no_rating(): void {
        Redis::spy();

        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'body' => 'Thanks for the review!',
            ])->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'parent_id' => $root->id,
            'rating' => null,
            'body' => 'Thanks for the review!',
        ]);

        Redis::shouldHaveReceived('publish')
            ->withArgs(function (string $channel, string $payload) use ($product, $root) {
                $decoded = json_decode($payload, true);

                return $channel === "reviews.products.{$product->slug}"
                    && $decoded['event'] === 'review.created'
                    && $decoded['data']['parent_id'] === $root->id;
            })
            ->once();
    }

    public function test_store_rejects_a_parent_id_that_is_itself_a_reply(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $reply = $this->createReview($product, $this->createUser(), ['parent_id' => $root->id, 'rating' => null]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $reply->id,
                'body' => 'Replying to a reply.',
            ])->assertStatus(422);
    }

    public function test_store_rejects_a_parent_id_belonging_to_another_product(): void {
        $product = $this->createProduct();
        $other_product = $this->createProduct();
        $root = $this->createReview($other_product, $this->createUser());
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'body' => 'Wrong product.',
            ])->assertStatus(422);
    }

    public function test_store_allows_a_reply_from_a_user_who_already_has_a_root_review(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $this->createReview($product, $user);
        $root = $this->createReview($product, $this->createUser());

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'body' => 'Replying despite already having my own review.',
            ])->assertSuccessful();
    }

    public function test_store_creates_a_reply_addressing_a_specific_reply(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $first_reply = $this->createReview($product, $this->createUser(), ['parent_id' => $root->id, 'rating' => null]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'replied_to_comment_id' => $first_reply->id,
                'body' => 'Replying to your reply specifically.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'parent_id' => $root->id,
            'replied_to_comment_id' => $first_reply->id,
        ]);
    }

    public function test_store_rejects_a_replied_to_comment_id_from_a_different_thread(): void {
        $product = $this->createProduct();
        $root_a = $this->createReview($product, $this->createUser());
        $root_b = $this->createReview($product, $this->createUser());
        $reply_under_b = $this->createReview($product, $this->createUser(), ['parent_id' => $root_b->id, 'rating' => null]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root_a->id,
                'replied_to_comment_id' => $reply_under_b->id,
                'body' => 'Wrong thread.',
            ])->assertStatus(422);
    }

    public function test_store_rejects_a_replied_to_comment_id_that_is_a_root_review(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'replied_to_comment_id' => $root->id,
                'body' => 'Targeting the root itself, not a reply.',
            ])->assertStatus(422);
    }

    public function test_update_edits_a_reply_without_requiring_a_rating(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $user = $this->createUser();
        $reply = $this->createReview($product, $user, ['parent_id' => $root->id, 'rating' => null, 'body' => 'Old reply.']);

        $this->actingAs($user, 'web')
            ->putJson("/api/products/{$product->slug}/reviews/{$reply->id}", [
                'body' => 'Updated reply.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'id' => $reply->id,
            'rating' => null,
            'body' => 'Updated reply.',
        ]);
    }

    public function test_destroy_deletes_a_reply(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $user = $this->createUser();
        $reply = $this->createReview($product, $user, ['parent_id' => $root->id, 'rating' => null]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/products/{$product->slug}/reviews/{$reply->id}")
            ->assertSuccessful();

        $this->assertSoftDeleted('reviews', ['id' => $reply->id]);
    }

    public function test_react_toggles_a_reaction_on_a_reply(): void {
        $product = $this->createProduct();
        $root = $this->createReview($product, $this->createUser());
        $reply = $this->createReview($product, $this->createUser(), ['parent_id' => $root->id, 'rating' => null]);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$reply->id}/react", ['type' => 'like'])
            ->assertSuccessful()
            ->assertJsonPath('data.likes_count', 1)
            ->assertJsonPath('data.parent_id', $root->id);
    }

    public function test_store_requires_authentication(): void {
        $product = $this->createProduct();

        $this->postJson("/api/products/{$product->slug}/reviews", [
            'rating' => 5,
            'body' => 'Great book.',
        ])->assertUnauthorized();
    }

    public function test_store_creates_a_review(): void {
        Redis::spy();

        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => 'Really enjoyed this book.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'Really enjoyed this book.',
        ]);
    }

    public function test_store_sanitizes_the_body_to_a_safe_tag_allowlist(): void {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => '<script>alert(1)</script><b onmouseover="alert(2)">Great</b> <img src=x onerror=alert(3)> book<br>indeed',
            ])->assertSuccessful();

        // <script>/<img> tags and every attribute are stripped; a disallowed tag's own
        // text content (not code) is left behind as inert plain text, same as strip_tags().
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'body' => 'alert(1)<b>Great</b>  book<br>indeed',
        ]);
    }

    public function test_store_rejects_a_body_that_is_only_formatting_tags(): void {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => '<b></b><br>',
            ])->assertStatus(422);
    }

    public function test_store_publishes_to_redis(): void {
        Redis::spy();

        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => 'Really enjoyed this book.',
            ])->assertSuccessful();

        Redis::shouldHaveReceived('publish')
            ->withArgs(fn (string $channel, string $payload) => $channel === "reviews.products.{$product->slug}"
                && json_decode($payload, true)['event'] === 'review.created')
            ->once();
    }

    public function test_store_rejects_a_second_review_from_the_same_user(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $this->createReview($product, $user);

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 3,
                'body' => 'Another attempt.',
            ])->assertStatus(403);
    }

    public function test_store_validates_rating_range(): void {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 6,
                'body' => 'Great book.',
            ])->assertStatus(422);
    }

    public function test_store_validates_body_presence(): void {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 5,
                'body' => '',
            ])->assertStatus(422);
    }

    public function test_store_rejects_a_body_over_1000_characters(): void {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => str_repeat('a', 1001),
            ])->assertStatus(422);
    }

    public function test_update_allows_owner_within_24_hours(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $review = $this->createReview($product, $user, ['rating' => 3, 'body' => 'Old body.']);

        $this->actingAs($user, 'web')
            ->putJson("/api/products/{$product->slug}/reviews/{$review->id}", [
                'rating' => 5,
                'body' => 'Updated body.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'body' => 'Updated body.',
        ]);
    }

    public function test_update_rejects_after_24_hours(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $review = $this->createReview($product, $user);
        $review->created_at = now()->subDays(2);
        $review->save();

        $this->actingAs($user, 'web')
            ->putJson("/api/products/{$product->slug}/reviews/{$review->id}", [
                'rating' => 5,
                'body' => 'Too late to edit.',
            ])->assertStatus(403);
    }

    public function test_update_rejects_a_non_owner(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $other = $this->createUser();
        $review = $this->createReview($product, $owner);

        $this->actingAs($other, 'web')
            ->putJson("/api/products/{$product->slug}/reviews/{$review->id}", [
                'rating' => 1,
                'body' => 'Not mine.',
            ])->assertStatus(403);
    }

    public function test_destroy_deletes_own_review_and_allows_a_new_one(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $review = $this->createReview($product, $user);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/products/{$product->slug}/reviews/{$review->id}")
            ->assertSuccessful();

        $this->assertSoftDeleted('reviews', ['id' => $review->id]);

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 4,
                'body' => 'Second time around.',
            ])->assertSuccessful();
    }

    public function test_destroy_rejects_a_non_owner(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $other = $this->createUser();
        $review = $this->createReview($product, $owner);

        $this->actingAs($other, 'web')
            ->deleteJson("/api/products/{$product->slug}/reviews/{$review->id}")
            ->assertStatus(403);
    }

    public function test_react_like_increments_likes_count(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", [
                'type' => 'like',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.likes_count', 1)
            ->assertJsonPath('data.reaction', 'like');
    }

    public function test_react_toggles_off_when_clicking_same_reaction_again(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful()
            ->assertJsonPath('data.likes_count', 0)
            ->assertJsonPath('data.reaction', null);
    }

    public function test_react_switches_from_dislike_to_like(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'dislike']);

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful()
            ->assertJsonPath('data.likes_count', 1)
            ->assertJsonPath('data.dislikes_count', 0)
            ->assertJsonPath('data.reaction', 'like');
    }

    public function test_react_publishes_to_redis(): void {
        Redis::spy();

        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful();

        Redis::shouldHaveReceived('publish')
            ->withArgs(fn (string $channel, string $payload) => $channel === "reviews.products.{$product->slug}"
                && json_decode($payload, true)['event'] === 'review.liked')
            ->once();
    }

    public function test_product_rating_and_reviews_count_recalculate_on_create(): void {
        $product = $this->createProduct(null, ['rating_avg' => 0]);
        $this->createReview($product, $this->createUser(), ['rating' => 4]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'rating' => 2,
                'body' => 'Not as good.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'rating_avg' => 3.0,
            'reviews_count' => 2,
        ]);
    }

    public function test_product_rating_recalculates_on_delete(): void {
        $product = $this->createProduct();
        $this->createReview($product, $this->createUser(), ['rating' => 4]);
        $user = $this->createUser();
        $review = $this->createReview($product, $user, ['rating' => 2]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/products/{$product->slug}/reviews/{$review->id}")
            ->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'rating_avg' => 4.0,
            'reviews_count' => 1,
        ]);
    }

    public function test_product_rating_resets_to_null_when_last_review_is_deleted(): void {
        $product = $this->createProduct();
        $user = $this->createUser();
        $review = $this->createReview($product, $user, ['rating' => 4]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/products/{$product->slug}/reviews/{$review->id}")
            ->assertSuccessful();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'rating_avg' => null,
            'reviews_count' => 0,
        ]);
    }

    public function test_report_requires_authentication(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());

        $this->postJson("/api/products/{$product->slug}/reviews/{$review->id}/report", [
            'reason' => 'Spam',
        ])->assertUnauthorized();
    }

    public function test_report_creates_a_review_report(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reporter = $this->createUser();

        $this->actingAs($reporter, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/report", [
                'reason' => 'Spam',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.reported', true);

        $this->assertDatabaseHas('review_reports', [
            'review_id' => $review->id,
            'user_id' => $reporter->id,
            'reason' => 'Spam',
        ]);
    }

    public function test_report_accepts_no_reason(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reporter = $this->createUser();

        $this->actingAs($reporter, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/report", [])
            ->assertSuccessful();

        $this->assertDatabaseHas('review_reports', [
            'review_id' => $review->id,
            'user_id' => $reporter->id,
            'reason' => null,
        ]);
    }

    public function test_reporting_the_same_review_twice_updates_the_existing_report(): void {
        $product = $this->createProduct();
        $review = $this->createReview($product, $this->createUser());
        $reporter = $this->createUser();

        $this->actingAs($reporter, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/report", [
                'reason' => 'Spam',
            ])->assertSuccessful();

        $this->actingAs($reporter, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/report", [
                'reason' => 'Harassment',
            ])->assertSuccessful();

        $this->assertDatabaseCount('review_reports', 1);
        $this->assertDatabaseHas('review_reports', [
            'review_id' => $review->id,
            'user_id' => $reporter->id,
            'reason' => 'Harassment',
        ]);
    }
}

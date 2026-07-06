<?php

namespace Tests\Feature;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_reply_creates_a_notification_for_the_root_review_owner(): void {
        Redis::spy();

        $product = $this->createProduct();
        $owner = $this->createUser();
        $root = $this->createReview($product, $owner);
        $replier = $this->createUser();

        $this->actingAs($replier, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'body' => 'Thanks for the tip!',
            ])->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $owner->id,
            'type' => 'reply',
            'parent_id' => $root->id,
        ]);

        Redis::shouldHaveReceived('publish')
            ->withArgs(function (string $channel, string $payload) use ($owner) {
                $decoded = json_decode($payload, true);

                return $channel === "notifications.users.{$owner->public_id}"
                    && $decoded['event'] === 'notification'
                    && $decoded['data']['action'] === 'upserted';
            })
            ->once();
    }

    public function test_replying_to_your_own_review_does_not_create_a_notification(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $root = $this->createReview($product, $owner);

        $this->actingAs($owner, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'body' => 'Replying to myself.',
            ])->assertSuccessful();

        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_replying_to_a_specific_reply_notifies_that_replys_author_not_the_root_owner(): void {
        $product = $this->createProduct();
        $root_owner = $this->createUser();
        $root = $this->createReview($product, $root_owner);
        $reply_author = $this->createUser();
        $first_reply = $this->createReview($product, $reply_author, ['parent_id' => $root->id, 'rating' => null]);
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->postJson("/api/products/{$product->slug}/reviews", [
                'parent_id' => $root->id,
                'replied_to_comment_id' => $first_reply->id,
                'body' => 'Addressed reply.',
            ])->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $reply_author->id,
            'type' => 'reply',
        ]);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $root_owner->id]);
    }

    public function test_reacting_to_a_review_creates_a_notification_for_its_owner(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $owner->id,
            'from_user_id' => $reactor->id,
            'review_id' => $review->id,
            'type' => 'like',
        ]);
    }

    public function test_reacting_to_your_own_review_does_not_create_a_notification(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);

        $this->actingAs($owner, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful();

        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_toggling_the_same_reaction_off_deletes_the_notification(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $notification = UserNotification::where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like'])
            ->assertSuccessful();

        $this->assertSoftDeleted('user_notifications', ['id' => $notification->id]);
        $this->assertEquals(0, UserNotification::count());
    }

    public function test_switching_reaction_updates_the_existing_notification_and_keeps_it_read(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $notification = UserNotification::where('user_id', $owner->id)->firstOrFail();
        $notification->update(['read_at' => now()]);

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'dislike'])
            ->assertSuccessful();

        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseHas('user_notifications', [
            'id' => $notification->id,
            'type' => 'dislike',
        ]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_index_returns_top_notifications_and_auto_marks_them_read(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);

        foreach (range(1, 3) as $i) {
            $this->actingAs($this->createUser(), 'web')
                ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);
            $this->app['auth']->forgetGuards();
        }

        $this->actingAs($owner, 'web')
            ->getJson('/api/notifications')
            ->assertSuccessful()
            ->assertJsonPath('data.unread_count', 0);

        $this->assertDatabaseMissing('user_notifications', ['user_id' => $owner->id, 'read_at' => null]);
    }

    public function test_unread_count_endpoint_reflects_unread_notifications(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $this->app['auth']->forgetGuards();

        $this->actingAs($owner, 'web')
            ->getJson('/api/notifications/unread-count')
            ->assertSuccessful()
            ->assertJsonPath('data.count', 1);
    }

    public function test_mark_read_is_idempotent_and_ownership_checked(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $notification = UserNotification::where('user_id', $owner->id)->firstOrFail();
        $other_user = $this->createUser();

        $this->app['auth']->forgetGuards();
        $this->actingAs($other_user, 'web')
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertSuccessful();
        $this->assertNull($notification->fresh()->read_at);

        $this->app['auth']->forgetGuards();
        $this->actingAs($owner, 'web')
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertSuccessful();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($owner, 'web')
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertSuccessful();
    }

    public function test_notifications_require_authentication(): void {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->getJson('/api/notifications/all')->assertUnauthorized();
        $this->getJson('/api/notifications/unread-count')->assertUnauthorized();
        $this->patchJson('/api/notifications/1/read')->assertUnauthorized();
    }

    public function test_all_paginated_marks_the_fetched_page_read(): void {
        $product = $this->createProduct();
        $owner = $this->createUser();
        $review = $this->createReview($product, $owner);
        $reactor = $this->createUser();

        $this->actingAs($reactor, 'web')
            ->postJson("/api/products/{$product->slug}/reviews/{$review->id}/react", ['type' => 'like']);

        $this->app['auth']->forgetGuards();

        $this->actingAs($owner, 'web')
            ->getJson('/api/notifications/all')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items.data');

        $this->assertDatabaseMissing('user_notifications', ['user_id' => $owner->id, 'read_at' => null]);
    }
}

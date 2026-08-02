<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_cart_returns_successful_response(): void {
        $this->getJson('/api/pages/cart')->assertSuccessful();
    }

    public function test_cart_returns_null_contact_for_a_guest(): void {
        $this->getJson('/api/pages/cart')->assertSuccessful()->assertJsonPath('data.contact', null);
    }

    public function test_cart_returns_the_latest_contact_for_an_authenticated_user(): void {
        $user = $this->createUser();
        $delivery = $this->createDeliveryService(['key' => 'nova_poshta']);
        $branch = $this->createDeliveryBranch($delivery);

        $this->createOrderContact([
            'user_id' => $user->id,
            'email' => 'older@example.com',
            'last_ordered_at' => now()->subDay(),
        ]);

        $this->createOrderContact([
            'user_id' => $user->id,
            'email' => 'newer@example.com',
            'delivery_id' => $delivery->id,
            'delivery_branch_id' => $branch->id,
            'last_ordered_at' => now(),
        ]);

        $this->actingAs($user)->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonPath('data.contact.email', 'newer@example.com')
            ->assertJsonPath('data.contact.delivery_id', $delivery->id)
            ->assertJsonPath('data.contact.branch_id', $branch->id);
    }

    public function test_cart_does_not_return_another_users_contact(): void {
        $owner = $this->createUser();
        $viewer = $this->createUser();
        $this->createOrderContact(['user_id' => $owner->id, 'email' => 'owner@example.com']);

        $this->actingAs($viewer)->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonPath('data.contact', null);
    }

    public function test_cart_delivery_options_excludes_inactive_delivery_services(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup', 'name' => 'Pickup']);
        $this->createDeliveryService(['key' => 'disabled', 'name' => 'Disabled', 'status' => 0]);

        $this->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.delivery_options')
            ->assertJsonPath('data.delivery_options.0.id', $delivery->id);
    }

    public function test_cart_delivery_options_marks_requires_branch_false_when_delivery_has_no_branches(): void {
        $this->createDeliveryService(['key' => 'pickup', 'name' => 'Pickup']);

        $this->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonPath('data.delivery_options.0.requires_branch', false)
            ->assertJsonPath('data.delivery_options.0.branches', []);
    }

    public function test_cart_delivery_options_marks_requires_branch_true_and_lists_active_branches(): void {
        $delivery = $this->createDeliveryService(['key' => 'nova_poshta', 'name' => 'Nova Poshta']);
        $branch = $this->createDeliveryBranch($delivery, ['city' => 'Kyiv', 'branch' => 'Branch #1']);
        $this->createDeliveryBranch($delivery, ['city' => 'Lviv', 'branch' => 'Branch #2', 'status' => 0]);

        $this->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonPath('data.delivery_options.0.requires_branch', true)
            ->assertJsonCount(1, 'data.delivery_options.0.branches')
            ->assertJsonPath('data.delivery_options.0.branches.0.id', $branch->id)
            ->assertJsonPath('data.delivery_options.0.branches.0.city', 'Kyiv');
    }

    public function test_cart_delivery_options_cache_is_invalidated_on_delivery_branch_write(): void {
        $delivery = $this->createDeliveryService(['key' => 'nova_poshta', 'name' => 'Nova Poshta']);
        $branch = $this->createDeliveryBranch($delivery, ['city' => 'Kyiv', 'branch' => 'Original branch']);

        $this->assertCacheInvalidatedOnWrite('/api/pages/cart', $branch, 'data.delivery_options.0.branches.0.branch', 'Original branch', [
            'branch' => 'Updated branch',
        ], 'Updated branch');
    }

    public function test_cart_payment_options_excludes_inactive_payments(): void {
        $payment = $this->createPayment(['key' => 'card', 'name' => 'Pay by Card Online']);
        $this->createPayment(['key' => 'disabled', 'name' => 'Disabled', 'status' => 0]);

        $this->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.payment_options')
            ->assertJsonPath('data.payment_options.0.key', $payment->key)
            ->assertJsonPath('data.payment_options.0.name', $payment->name);
    }

    public function test_cart_payment_options_cache_is_invalidated_on_payment_write(): void {
        $payment = $this->createPayment(['key' => 'card', 'name' => 'Original name']);

        $this->assertCacheInvalidatedOnWrite('/api/pages/cart', $payment, 'data.payment_options.0.name', 'Original name', [
            'name' => 'Updated name',
        ], 'Updated name');
    }
}

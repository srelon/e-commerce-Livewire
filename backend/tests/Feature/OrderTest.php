<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    protected function validPayload(array $overrides = []): array {
        return array_merge([
            'contact' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '555-0100',
                'email' => 'john@example.com',
            ],
            'delivery' => [
                'delivery_id' => 0,
            ],
            'payment' => [
                'method' => 'cash',
            ],
        ], $overrides);
    }

    public function test_guest_can_place_an_order_with_valid_data(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, ['quantity' => 5]);

        $response = $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [
                ['slug' => $product->slug, 'quantity' => 2],
            ],
        ]));

        $response->assertSuccessful();
        $public_id = $response->json('data.public_id');
        $this->assertMatchesRegularExpression('/^\d{8}$/', $public_id);

        $this->assertDatabaseHas('orders', [
            'public_id' => $public_id,
            'user_id' => null,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('order_contacts', [
            'email' => 'john@example.com',
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_stock_id' => $stock->id,
            'quantity' => 2,
            'price' => $stock->price,
        ]);
    }

    public function test_pickup_order_is_accepted_without_a_branch_id(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $response = $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [
                ['slug' => $product->slug, 'quantity' => 1],
            ],
        ]));

        $response->assertSuccessful();
    }

    public function test_order_resolves_nova_poshta_branch_by_id(): void {
        $this->createPayment(['key' => 'cash']);
        $delivery = $this->createDeliveryService(['key' => 'nova_poshta']);
        $branch = $this->createDeliveryBranch($delivery, [
            'city' => 'Kyiv',
            'branch' => 'Branch #1, Khreshchatyk St, 22',
        ]);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $response = $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => [
                'delivery_id' => $delivery->id,
                'branch_id' => $branch->id,
            ],
            'items' => [
                ['slug' => $product->slug, 'quantity' => 1],
            ],
        ]));

        $response->assertSuccessful();
        $this->assertDatabaseHas('orders', [
            'public_id' => $response->json('data.public_id'),
            'delivery_branch_id' => $branch->id,
        ]);
    }

    public function test_order_requires_a_branch_id_when_the_delivery_method_has_branches(): void {
        $this->createPayment(['key' => 'cash']);
        $delivery = $this->createDeliveryService(['key' => 'nova_poshta']);
        $this->createDeliveryBranch($delivery);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $response = $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [
                ['slug' => $product->slug, 'quantity' => 1],
            ],
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('delivery.branch_id');
    }

    public function test_order_is_rejected_when_requested_quantity_exceeds_available_stock(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 1]);

        $response = $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [
                ['slug' => $product->slug, 'quantity' => 2],
            ],
        ]));

        $response->assertStatus(422)
            ->assertJsonPath('errors', 'The available quantity of some items has changed.')
            ->assertJsonPath('items.0.slug', $product->slug)
            ->assertJsonPath('items.0.available', 1);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_requires_at_least_one_item(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);

        $this->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    public function test_placing_a_second_order_with_the_same_contact_reuses_the_same_order_contact_row(): void {
        $user = $this->createUser();
        $pickup = $this->createDeliveryService(['key' => 'pickup']);
        $nova_poshta = $this->createDeliveryService(['key' => 'nova_poshta']);
        $branch = $this->createDeliveryBranch($nova_poshta);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $this->actingAs($user)->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $pickup->id],
            'items' => [['slug' => $product->slug, 'quantity' => 1]],
        ]))->assertSuccessful();

        $this->assertDatabaseCount('order_contacts', 1);

        $this->actingAs($user)->postJson('/api/cart/orders', $this->validPayload([
            'delivery' => ['delivery_id' => $nova_poshta->id, 'branch_id' => $branch->id],
            'items' => [['slug' => $product->slug, 'quantity' => 1]],
        ]))->assertSuccessful();

        $this->assertDatabaseCount('order_contacts', 1);
        $this->assertDatabaseHas('order_contacts', [
            'user_id' => $user->id,
            'email' => 'john@example.com',
            'delivery_id' => $nova_poshta->id,
            'delivery_branch_id' => $branch->id,
        ]);
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_reordering_an_earlier_contact_marks_it_as_the_most_recently_used(): void {
        $user = $this->createUser();
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $contact_a = [
            'first_name' => 'Alice',
            'last_name' => 'Anderson',
            'phone' => '555-0111',
            'email' => 'alice@example.com',
        ];
        $contact_b = [
            'first_name' => 'Bob',
            'last_name' => 'Brown',
            'phone' => '555-0222',
            'email' => 'bob@example.com',
        ];

        $order = fn (array $contact) => $this->validPayload([
            'contact' => $contact,
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [['slug' => $product->slug, 'quantity' => 1]],
        ]);

        // Same delivery every time, so re-ordering contact A a second time changes
        // nothing about its own columns — only `last_ordered_at` should move it back to the front.
        $this->actingAs($user)->postJson('/api/cart/orders', $order($contact_a))->assertSuccessful();
        $this->actingAs($user)->postJson('/api/cart/orders', $order($contact_b))->assertSuccessful();
        $this->actingAs($user)->postJson('/api/cart/orders', $order($contact_a))->assertSuccessful();

        $this->assertDatabaseCount('order_contacts', 2);

        $this->actingAs($user)->getJson('/api/pages/cart')
            ->assertSuccessful()
            ->assertJsonPath('data.contact.email', 'alice@example.com');
    }

    public function test_different_users_with_the_same_contact_details_get_separate_order_contact_rows(): void {
        $user_a = $this->createUser();
        $user_b = $this->createUser();
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $payload = $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [['slug' => $product->slug, 'quantity' => 1]],
        ]);

        $this->actingAs($user_a)->postJson('/api/cart/orders', $payload)->assertSuccessful();
        $this->actingAs($user_b)->postJson('/api/cart/orders', $payload)->assertSuccessful();

        $this->assertDatabaseCount('order_contacts', 2);
    }

    public function test_guest_orders_with_matching_contact_details_reuse_the_same_order_contact_row(): void {
        $delivery = $this->createDeliveryService(['key' => 'pickup']);
        $this->createPayment(['key' => 'cash']);
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 5]);

        $payload = $this->validPayload([
            'delivery' => ['delivery_id' => $delivery->id],
            'items' => [['slug' => $product->slug, 'quantity' => 1]],
        ]);

        $this->postJson('/api/cart/orders', $payload)->assertSuccessful();
        $this->postJson('/api/cart/orders', $payload)->assertSuccessful();

        $this->assertDatabaseCount('order_contacts', 1);
        $this->assertDatabaseCount('orders', 2);
    }
}

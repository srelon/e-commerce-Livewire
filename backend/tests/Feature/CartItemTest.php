<?php

namespace Tests\Feature;

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class CartItemTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_store_requires_authentication(): void {
        $product = $this->createProduct();
        $this->createProductStock($product);

        $this->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 1])
            ->assertUnauthorized();
    }

    public function test_store_creates_a_cart_and_item_for_a_first_time_buyer(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 2])
            ->assertSuccessful()
            ->assertJsonPath('data.quantity', 2);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(2, $cart->items()->first()->quantity);
    }

    public function test_store_reuses_the_existing_cart_on_a_second_add(): void {
        $user = $this->createUser();
        $first_product = $this->createProduct();
        $this->createProductStock($first_product);
        $second_product = $this->createProduct();
        $this->createProductStock($second_product);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $first_product->slug, 'quantity' => 1])->assertSuccessful();
        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $second_product->slug, 'quantity' => 1])->assertSuccessful();

        $this->assertSame(1, Cart::where('user_id', $user->id)->count());
    }

    public function test_store_overwrites_quantity_when_the_same_product_is_added_again(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 1])->assertSuccessful();

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 3])
            ->assertSuccessful()
            ->assertJsonPath('data.quantity', 3);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(3, $cart->items()->first()->quantity);
    }

    public function test_store_reactivates_a_previously_removed_item_instead_of_duplicating_it(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 2])->assertSuccessful();
        $cart = Cart::where('user_id', $user->id)->first();
        $original_item_id = $cart->items()->first()->id;

        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 5])
            ->assertSuccessful()
            ->assertJsonPath('data.quantity', 5);

        $this->assertSame(1, $cart->items()->count());
        $reactivated_item = $cart->items()->where('status', 0)->first();
        $this->assertSame($original_item_id, $reactivated_item->id);
        $this->assertSame(5, $reactivated_item->quantity);
    }

    public function test_store_rejects_an_unknown_slug(): void {
        $user = $this->createUser();

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => 'unknown-slug', 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_store_rejects_a_quantity_below_one(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 0])
            ->assertStatus(422);
    }

    public function test_store_rejects_a_quantity_exceeding_available_stock(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 2]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 5])
            ->assertStatus(422)
            ->assertJsonPath('items.0.slug', $product->slug)
            ->assertJsonPath('items.0.available', 2);

        $this->assertSame(0, Cart::where('user_id', $user->id)->count());
    }

    public function test_sync_creates_missing_items(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/sync', [
            'items' => [
                ['slug' => $product->slug, 'quantity' => 3],
            ],
        ])->assertSuccessful()->assertJsonPath('data.conflicts', []);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(3, $cart->items()->first()->quantity);
    }

    public function test_sync_reactivates_a_previously_removed_item_instead_of_duplicating_it(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 2])->assertSuccessful();
        $cart = Cart::where('user_id', $user->id)->first();
        $original_item_id = $cart->items()->first()->id;

        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $this->actingAs($user)->postJson('/api/cart/sync', [
            'items' => [
                ['slug' => $product->slug, 'quantity' => 4],
            ],
        ])->assertSuccessful();

        $this->assertSame(1, $cart->items()->count());
        $reactivated_item = $cart->items()->where('status', 0)->first();
        $this->assertSame($original_item_id, $reactivated_item->id);
        $this->assertSame(4, $reactivated_item->quantity);
    }

    public function test_sync_overwrites_the_quantity_of_an_existing_item_instead_of_adding(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 5])->assertSuccessful();

        $this->actingAs($user)->postJson('/api/cart/sync', [
            'items' => [
                ['slug' => $product->slug, 'quantity' => 2],
            ],
        ])->assertSuccessful();

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(2, $cart->items()->first()->quantity);
    }

    public function test_sync_reports_a_conflict_for_an_item_exceeding_available_stock_but_still_syncs_the_rest(): void {
        $user = $this->createUser();
        $ok_product = $this->createProduct();
        $this->createProductStock($ok_product, ['quantity' => 10]);
        $short_product = $this->createProduct();
        $this->createProductStock($short_product, ['quantity' => 1]);

        $this->actingAs($user)->postJson('/api/cart/sync', [
            'items' => [
                ['slug' => $ok_product->slug, 'quantity' => 2],
                ['slug' => $short_product->slug, 'quantity' => 5],
            ],
        ])
            ->assertSuccessful()
            ->assertJsonPath('data.conflicts.0.slug', $short_product->slug)
            ->assertJsonPath('data.conflicts.0.available', 1);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame($ok_product->id, $cart->items()->first()->product_id);
    }

    public function test_destroy_requires_authentication(): void {
        $product = $this->createProduct();

        $this->deleteJson("/api/cart/items/{$product->slug}")->assertUnauthorized();
    }

    public function test_destroy_marks_the_item_as_deleted_without_removing_the_row(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 2])->assertSuccessful();

        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(0, $cart->items()->where('status', 0)->count());
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'status' => 4,
        ]);
    }

    public function test_destroy_excludes_the_removed_item_from_profile_cart(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 1])->assertSuccessful();
        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $this->actingAs($user, 'web')
            ->getJson('/api/auth/profile')
            ->assertSuccessful()
            ->assertJsonPath('data.cart', []);
    }

    public function test_destroy_is_idempotent_for_an_already_removed_item(): void {
        $user = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 1])->assertSuccessful();

        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();
        $this->actingAs($user)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertSame(1, $cart->items()->where('status', 4)->count());
    }

    public function test_destroy_is_a_noop_for_an_unknown_slug(): void {
        $user = $this->createUser();

        $this->actingAs($user)->deleteJson('/api/cart/items/unknown-slug')->assertSuccessful();
    }

    public function test_destroy_does_not_affect_another_users_cart(): void {
        $owner = $this->createUser();
        $other = $this->createUser();
        $product = $this->createProduct();
        $this->createProductStock($product, ['quantity' => 10]);

        $this->actingAs($owner)->postJson('/api/cart/items', ['slug' => $product->slug, 'quantity' => 1])->assertSuccessful();

        $this->actingAs($other)->deleteJson("/api/cart/items/{$product->slug}")->assertSuccessful();

        $owner_cart = Cart::where('user_id', $owner->id)->first();
        $this->assertSame(1, $owner_cart->items()->where('status', 0)->count());
    }
}

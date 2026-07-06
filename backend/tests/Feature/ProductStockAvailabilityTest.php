<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class ProductStockAvailabilityTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_available_quantity_equals_stock_quantity_with_no_orders(): void {
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, [
            'quantity' => 20,
        ]);

        $this->getJson('/api/products')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.stock.id', $stock->id)
            ->assertJsonPath('data.items.data.0.stock.quantity', 20);
    }

    public function test_pending_order_items_reserve_by_their_ordered_quantity(): void {
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, [
            'quantity' => 20,
        ]);
        $this->createOrderItem($stock, [
            'status' => 0,
            'quantity' => 5,
            'fact_quantity' => 0,
        ]);

        $this->getJson('/api/products')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.stock.quantity', 15);
    }

    public function test_shipped_delivered_and_completed_order_items_reserve_by_fact_quantity_not_ordered_quantity(): void {
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, [
            'quantity' => 20,
        ]);
        $this->createOrderItem($stock, [
            'status' => 1,
            'quantity' => 10,
            'fact_quantity' => 4,
        ]);
        $this->createOrderItem($stock, [
            'status' => 2,
            'quantity' => 10,
            'fact_quantity' => 3,
        ]);
        $this->createOrderItem($stock, [
            'status' => 3,
            'quantity' => 10,
            'fact_quantity' => 2,
        ]);

        $this->getJson('/api/products')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.stock.quantity', 11);
    }

    public function test_cancelled_order_items_do_not_reserve_any_quantity(): void {
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, [
            'quantity' => 20,
        ]);
        $this->createOrderItem($stock, [
            'status' => 4,
            'quantity' => 15,
            'fact_quantity' => 15,
        ]);

        $this->getJson('/api/products')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.stock.quantity', 20);
    }

    public function test_available_quantity_never_goes_below_zero(): void {
        $product = $this->createProduct();
        $stock = $this->createProductStock($product, [
            'quantity' => 5,
        ]);
        $this->createOrderItem($stock, [
            'status' => 0,
            'quantity' => 10,
            'fact_quantity' => 0,
        ]);

        $this->getJson('/api/products')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.stock.quantity', 0);
    }
}

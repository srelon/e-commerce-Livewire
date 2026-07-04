<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_product_show_returns_successful_response(): void
    {
        $product = $this->createProduct();

        $this->getJson("/api/products/{$product->slug}")->assertSuccessful();
    }

    public function test_product_show_returns_full_product_details(): void
    {
        $category = $this->createCategory([
            'name' => 'Fantasy',
        ]);
        $author = $this->createAuthor([
            'name' => 'Test Author',
        ]);
        $product = $this->createProduct($category, [
            'title' => 'Astral Journey',
            'author_id' => $author->id,
            'short_description' => 'Short desc.',
            'description' => 'Long desc.',
            'rating_avg' => 4.5,
            'reviews_count' => 3,
        ]);
        $stock = $this->createProductStock($product, [
            'price' => '28.00',
            'before_price' => '35.00',
            'quantity' => 20,
        ]);
        $this->createProductImage($product, [
            'sort_order' => 1,
        ]);
        $this->createProductImage($product, [
            'image' => [
                'original' => 'products/test-cover-2.webp',
            ],
            'sort_order' => 2,
        ]);

        $this->getJson("/api/products/{$product->slug}")
            ->assertSuccessful()
            ->assertJsonPath('data.title', 'Astral Journey')
            ->assertJsonPath('data.author', 'Test Author')
            ->assertJsonPath('data.category', 'Fantasy')
            ->assertJsonPath('data.stock.price', '28.00')
            ->assertJsonPath('data.stock.before_price', '35.00')
            ->assertJsonPath('data.rating', 4.5)
            ->assertJsonPath('data.reviews_count', 3)
            ->assertJsonPath('data.short_description', 'Short desc.')
            ->assertJsonPath('data.description', 'Long desc.')
            ->assertJsonCount(2, 'data.images')
            ->assertJsonPath('data.stock.id', $stock->id)
            ->assertJsonPath('data.stock.quantity', 20)
            ->assertJsonPath('data.seo.title', 'Astral Journey');
    }

    public function test_product_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/products/does-not-exist')->assertNotFound();
    }

    public function test_product_show_returns_404_for_a_deleted_product(): void
    {
        $product = $this->createProduct(null, [
            'status' => 4,
        ]);

        $this->getJson("/api/products/{$product->slug}")->assertNotFound();
    }

    public function test_product_show_is_reachable_when_archived(): void
    {
        $product = $this->createProduct(null, [
            'status' => 2,
        ]);

        $this->getJson("/api/products/{$product->slug}")->assertSuccessful();
    }

    public function test_product_show_includes_related_products_from_the_same_category(): void
    {
        $category = $this->createCategory();
        $other_category = $this->createCategory();
        $product = $this->createProduct($category, [
            'title' => 'Main Book',
        ]);
        $related = $this->createProduct($category, [
            'title' => 'Related Book',
        ]);
        $this->createProduct($other_category, [
            'title' => 'Unrelated Book',
        ]);

        $this->getJson("/api/products/{$product->slug}")
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.related')
            ->assertJsonPath('data.related.0.title', 'Related Book');
    }
}

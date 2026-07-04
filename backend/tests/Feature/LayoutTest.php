<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_layout_returns_successful_response(): void
    {
        $this->getJson('/api/layout')->assertSuccessful();
    }

    public function test_layout_includes_active_categories_with_product_count(): void
    {
        $category = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $this->createProduct($category);

        $this->getJson('/api/layout')
            ->assertSuccessful()
            ->assertJsonPath('data.categories.0.slug', 'fantasy')
            ->assertJsonPath('data.categories.0.image', 'products_categories/test-promo.webp')
            ->assertJsonPath('data.categories.0.count', 1);
    }

    public function test_layout_includes_header_menu_with_nested_children(): void
    {
        $parent = $this->createMenuItem([
            'name' => 'Products',
            'route' => 'products',
        ]);
        $this->createMenuItem([
            'name' => 'Fantasy',
            'route' => 'products',
            'params' => [
                'category' => 'fantasy',
            ],
            'parent_id' => $parent->id,
        ]);

        $this->getJson('/api/layout')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.menu')
            ->assertJsonPath('data.menu.0.name', 'Products')
            ->assertJsonCount(1, 'data.menu.0.children')
            ->assertJsonPath('data.menu.0.children.0.name', 'Fantasy')
            ->assertJsonPath('data.menu.0.children.0.params.category', 'fantasy');
    }

    public function test_layout_excludes_footer_menu_items(): void
    {
        $this->createMenuItem([
            'location' => 'footer',
        ]);

        $this->getJson('/api/layout')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data.menu');
    }

    public function test_layout_includes_active_contacts_only(): void
    {
        $this->createContact([
            'key' => 'phone',
            'status' => 1,
        ]);
        $this->createContact([
            'key' => 'hidden',
            'status' => 0,
        ]);

        $this->getJson('/api/layout')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.contacts')
            ->assertJsonPath('data.contacts.0.key', 'phone');
    }

    public function test_layout_includes_best_selling_books(): void
    {
        $product = $this->createProduct(null, [
            'title' => 'Bestseller Book',
            'bestseller' => 1,
        ]);
        $this->createProductStock($product);

        $this->getJson('/api/layout')
            ->assertSuccessful()
            ->assertJsonPath('data.best_books.0.title', 'Bestseller Book');
    }

    public function test_layout_cache_is_invalidated_on_product_write(): void
    {
        $product = $this->createProduct(null, [
            'title' => 'Original Title',
            'bestseller' => 1,
        ]);
        $this->createProductStock($product);

        $this->assertCacheInvalidatedOnWrite('/api/layout', $product, 'data.best_books.0.title', 'Original Title', [
            'title' => 'Updated Title',
        ], 'Updated Title');
    }

    public function test_layout_cache_is_invalidated_on_contact_write(): void
    {
        $contact = $this->createContact([
            'key' => 'phone',
            'content' => 'old number',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/layout', $contact, 'data.contacts.0.content', 'old number', [
            'content' => 'new number',
        ], 'new number');
    }
}

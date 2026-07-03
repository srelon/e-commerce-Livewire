<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\ShopTestHelper;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase, ShopTestHelper;

    public function test_products_returns_successful_response(): void
    {
        $this->getJson('/api/products')->assertSuccessful();
    }

    public function test_products_are_filtered_by_category(): void
    {
        $fantasy = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $cooking = $this->createCategory([
            'name' => 'Cooking',
            'slug' => 'cooking',
        ]);
        $this->createProduct($fantasy, [
            'title' => 'Fantasy Book',
        ]);
        $this->createProduct($cooking, [
            'title' => 'Cooking Book',
        ]);

        $this->getJson('/api/products?category[]=Fantasy')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Fantasy Book');
    }

    public function test_products_accept_a_single_category_string_not_just_an_array(): void
    {
        $fantasy = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $this->createProduct($fantasy, [
            'title' => 'Fantasy Book',
        ]);

        $this->getJson('/api/products?category=Fantasy')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Fantasy Book');
    }

    public function test_products_accept_a_comma_separated_category_list(): void
    {
        $fantasy = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $cooking = $this->createCategory([
            'name' => 'Cooking',
            'slug' => 'cooking',
        ]);
        $history = $this->createCategory([
            'name' => 'History',
            'slug' => 'history',
        ]);
        $this->createProduct($fantasy, [
            'title' => 'Fantasy Book',
        ]);
        $this->createProduct($cooking, [
            'title' => 'Cooking Book',
        ]);
        $this->createProduct($history, [
            'title' => 'History Book',
        ]);

        $this->getJson('/api/products?category=Fantasy,Cooking')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.products');
    }

    public function test_products_are_filtered_by_author(): void
    {
        $winner = $this->createAuthor([
            'name' => 'Winner Author',
        ]);
        $loser = $this->createAuthor([
            'name' => 'Loser Author',
        ]);
        $this->createProduct(null, [
            'title' => 'By Winner',
            'author_id' => $winner->id,
        ]);
        $this->createProduct(null, [
            'title' => 'By Loser',
            'author_id' => $loser->id,
        ]);

        $this->getJson('/api/products?author[]=Winner Author')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'By Winner');
    }

    public function test_products_are_filtered_by_price_range(): void
    {
        $cheap = $this->createProduct(null, [
            'title' => 'Cheap Book',
        ]);
        $this->createProductStock($cheap, [
            'price' => '10.00',
        ]);
        $expensive = $this->createProduct(null, [
            'title' => 'Expensive Book',
        ]);
        $this->createProductStock($expensive, [
            'price' => '50.00',
        ]);

        $this->getJson('/api/products?price_min=20&price_max=60')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'Expensive Book');
    }

    public function test_products_ignore_a_non_numeric_price_min_or_max(): void
    {
        $this->createProduct();

        $this->getJson('/api/products?price_min=asdasd&price_max=alsogarbage')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products');
    }

    public function test_products_ignore_a_non_numeric_page(): void
    {
        $this->createProduct();

        $this->getJson('/api/products?page=asdasd')
            ->assertSuccessful()
            ->assertJsonPath('data.meta.current_page', 1);
    }

    public function test_products_are_filtered_by_rating(): void
    {
        $this->createProduct(null, [
            'title' => 'Low Rated',
            'rating_avg' => 3,
        ]);
        $this->createProduct(null, [
            'title' => 'High Rated',
            'rating_avg' => 5,
        ]);

        $this->getJson('/api/products?rating=4')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'High Rated');
    }

    public function test_products_are_filtered_by_status_on_sale(): void
    {
        $product = $this->createProduct(null, [
            'title' => 'On Sale Book',
        ]);
        $this->createProductStock($product, [
            'before_price' => '30.00',
            'price' => '20.00',
        ]);
        $regular = $this->createProduct(null, [
            'title' => 'Regular Book',
        ]);
        $this->createProductStock($regular);

        $this->getJson('/api/products?status[]=On Sale')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.title', 'On Sale Book');
    }

    public function test_products_are_sorted_by_price_ascending(): void
    {
        $expensive = $this->createProduct(null, [
            'title' => 'Expensive',
        ]);
        $this->createProductStock($expensive, [
            'price' => '50.00',
        ]);
        $cheap = $this->createProduct(null, [
            'title' => 'Cheap',
        ]);
        $this->createProductStock($cheap, [
            'price' => '10.00',
        ]);

        $this->getJson('/api/products?sort_by=price_asc')
            ->assertSuccessful()
            ->assertJsonPath('data.products.0.title', 'Cheap')
            ->assertJsonPath('data.products.1.title', 'Expensive');
    }

    public function test_products_response_includes_filter_groups_with_counts(): void
    {
        $category = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $this->createProduct($category);

        $response = $this->getJson('/api/products')->assertSuccessful();

        $groups = collect($response->json('data.filter_groups'));

        $this->assertSame('Filter by Price', $groups->firstWhere('query_key', 'price')['title']);

        $category_group = $groups->firstWhere('query_key', 'category');
        $this->assertSame('Fantasy', $category_group['items'][0]['name']);
        $this->assertSame(1, $category_group['items'][0]['count']);
    }

    public function test_filter_groups_exclude_categories_and_authors_with_no_products(): void
    {
        $with_products = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $this->createCategory([
            'name' => 'Empty Category',
            'slug' => 'empty-category',
        ]);
        $author = $this->createAuthor([
            'name' => 'Has Books',
        ]);
        $this->createAuthor([
            'name' => 'No Books',
        ]);
        $this->createProduct($with_products, [
            'author_id' => $author->id,
        ]);

        $response = $this->getJson('/api/products')->assertSuccessful();
        $groups = collect($response->json('data.filter_groups'));

        $category_names = collect($groups->firstWhere('query_key', 'category')['items'])->pluck('name');
        $author_names = collect($groups->firstWhere('query_key', 'author')['items'])->pluck('name');

        $this->assertTrue($category_names->contains('Fantasy'));
        $this->assertFalse($category_names->contains('Empty Category'));
        $this->assertTrue($author_names->contains('Has Books'));
        $this->assertFalse($author_names->contains('No Books'));
    }

    public function test_filter_groups_narrow_down_when_a_category_is_selected(): void
    {
        $fantasy = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $cooking = $this->createCategory([
            'name' => 'Cooking',
            'slug' => 'cooking',
        ]);
        $fantasy_author = $this->createAuthor([
            'name' => 'Fantasy Writer',
        ]);
        $cooking_author = $this->createAuthor([
            'name' => 'Cooking Writer',
        ]);
        $this->createProduct($fantasy, [
            'author_id' => $fantasy_author->id,
        ]);
        $this->createProduct($fantasy, [
            'author_id' => $fantasy_author->id,
        ]);
        $this->createProduct($cooking, [
            'author_id' => $cooking_author->id,
        ]);

        $response = $this->getJson('/api/products?category=Fantasy')->assertSuccessful();
        $groups = collect($response->json('data.filter_groups'));

        $author_group = $groups->firstWhere('query_key', 'author');
        $author_names = collect($author_group['items'])->pluck('name');

        $this->assertTrue($author_names->contains('Fantasy Writer'));
        $this->assertFalse($author_names->contains('Cooking Writer'));
        $this->assertSame(2, collect($author_group['items'])->firstWhere('name', 'Fantasy Writer')['count']);

        $category_group = $groups->firstWhere('query_key', 'category');
        $category_names = collect($category_group['items'])->pluck('name');
        $this->assertTrue($category_names->contains('Fantasy'));
        $this->assertTrue($category_names->contains('Cooking'), 'other categories stay visible so the user can switch');
    }

    public function test_filter_groups_keep_a_selected_item_visible_even_at_zero_count(): void
    {
        $fantasy = $this->createCategory([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);
        $cooking = $this->createCategory([
            'name' => 'Cooking',
            'slug' => 'cooking',
        ]);
        $fantasy_author = $this->createAuthor([
            'name' => 'Fantasy Writer',
        ]);
        $this->createProduct($fantasy, [
            'author_id' => $fantasy_author->id,
        ]);
        $this->createProduct($cooking);

        $response = $this->getJson('/api/products?category=Cooking&author=Fantasy Writer')->assertSuccessful();
        $groups = collect($response->json('data.filter_groups'));

        $author_group = $groups->firstWhere('query_key', 'author');
        $author_item = collect($author_group['items'])->firstWhere('name', 'Fantasy Writer');

        $this->assertNotNull($author_item, 'a selected author must stay visible so the user can unselect it');
        $this->assertSame(0, $author_item['count']);
    }

    public function test_status_filter_group_excludes_on_sale_when_no_product_is_on_sale(): void
    {
        $product = $this->createProduct();
        $this->createProductStock($product);

        $response = $this->getJson('/api/products')->assertSuccessful();
        $groups = collect($response->json('data.filter_groups'));

        $status_names = collect($groups->firstWhere('query_key', 'status')['items'])->pluck('name');

        $this->assertTrue($status_names->contains('In Stock'));
        $this->assertFalse($status_names->contains('On Sale'));
    }

    public function test_products_are_paginated_nine_per_page(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createProduct(null, [
                'title' => "Book {$i}",
            ]);
        }

        $response = $this->getJson('/api/products')->assertSuccessful();

        $response->assertJsonCount(9, 'data.products');
        $response->assertJsonPath('data.meta.total', 10);
        $response->assertJsonPath('data.meta.last_page', 2);
    }
}

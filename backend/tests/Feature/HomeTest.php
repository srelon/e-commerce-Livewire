<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_home_returns_successful_response(): void
    {
        $this->getJson('/api/pages/home')->assertSuccessful();
    }

    public function test_bestsellers_are_ordered_by_bestseller_then_rating(): void
    {
        $low = $this->createProduct(null, [
            'title' => 'Low',
            'bestseller' => 0,
            'rating_avg' => 3.0,
        ]);
        $this->createProductStock($low);
        $high = $this->createProduct(null, [
            'title' => 'High',
            'bestseller' => 1,
            'rating_avg' => 4.0,
        ]);
        $this->createProductStock($high);
        $highest = $this->createProduct(null, [
            'title' => 'Highest',
            'bestseller' => 1,
            'rating_avg' => 5.0,
        ]);
        $this->createProductStock($highest);

        $this->getJson('/api/pages/home')
            ->assertSuccessful()
            ->assertJsonPath('data.bestsellers.0.title', 'Highest')
            ->assertJsonPath('data.bestsellers.1.title', 'High')
            ->assertJsonPath('data.bestsellers.2.title', 'Low');
    }

    public function test_bestsellers_include_price_and_cover_image(): void
    {
        $product = $this->createProduct(null, [
            'bestseller' => 1,
        ]);
        $this->createProductStock($product, [
            'price' => '24.50',
        ]);
        $this->createProductImage($product, [
            'sort_order' => 2,
            'image' => [
                'original' => 'products/second.webp',
            ],
        ]);
        $this->createProductImage($product, [
            'sort_order' => 1,
            'image' => [
                'original' => 'products/first.webp',
            ],
        ]);

        $this->getJson('/api/pages/home')
            ->assertSuccessful()
            ->assertJsonPath('data.bestsellers.0.price', '24.50')
            ->assertJsonPath('data.bestsellers.0.image', 'products/first.webp');
    }

    public function test_best_author_is_the_one_with_most_bestseller_products(): void
    {
        $winner = $this->createAuthor([
            'name' => 'Winning Author',
            'content' => 'A great author.',
        ]);
        $this->createProduct(null, [
            'author_id' => $winner->id,
            'bestseller' => 1,
        ]);
        $this->createProduct(null, [
            'author_id' => $winner->id,
            'bestseller' => 1,
        ]);

        $loser = $this->createAuthor([
            'name' => 'Losing Author',
        ]);
        $this->createProduct(null, [
            'author_id' => $loser->id,
            'bestseller' => 1,
        ]);

        $this->getJson('/api/pages/home')
            ->assertSuccessful()
            ->assertJsonPath('data.best_author.name', 'Winning Author')
            ->assertJsonPath('data.best_author.content', 'A great author.');
    }

    public function test_best_rated_returns_up_to_nine_products_ordered_by_rating(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createProduct(null, [
                'title' => "Book {$i}",
                'rating_avg' => $i,
            ]);
        }

        $response = $this->getJson('/api/pages/home')->assertSuccessful();

        $response->assertJsonCount(9, 'data.best_rated');
        $response->assertJsonPath('data.best_rated.0.title', 'Book 10');
    }

    public function test_blog_returns_latest_seven_posts(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->createNewsPost([
                'title' => "Post {$i}",
                'slug' => "post-{$i}",
                'published_at' => now()->subDays(8 - $i),
            ]);
        }

        $response = $this->getJson('/api/pages/home')->assertSuccessful();

        $response->assertJsonCount(7, 'data.blog');
        $response->assertJsonPath('data.blog.0.title', 'Post 8');
    }

    public function test_blog_excludes_unpublished_posts(): void
    {
        $this->createNewsPost([
            'status' => 0,
        ]);

        $this->getJson('/api/pages/home')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data.blog');
    }

    public function test_home_includes_seo_for_the_home_page(): void
    {
        $this->assertPageSeoIncluded('/api/pages/home', 'home', 'Home');
    }

    public function test_home_cache_is_invalidated_on_product_stock_write(): void
    {
        $product = $this->createProduct(null, [
            'title' => 'Cached Book',
            'bestseller' => 1,
        ]);
        $stock = $this->createProductStock($product, [
            'price' => '10.00',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/home', $stock, 'data.bestsellers.0.price', '10.00', [
            'price' => '15.00',
        ], '15.00');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class NewsFilterTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_news_returns_successful_response(): void {
        $this->getJson('/api/news')->assertSuccessful();
    }

    public function test_news_excludes_unpublished_posts(): void {
        $this->createNewsPost([
            'title' => 'Published Post',
        ]);
        $this->createNewsPost([
            'title' => 'Draft Post',
            'status' => 0,
        ]);

        $this->getJson('/api/news')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items.data')
            ->assertJsonPath('data.items.data.0.title', 'Published Post');
    }

    public function test_news_are_sorted_by_date_added(): void {
        $this->createNewsPost([
            'title' => 'Older Post',
            'published_at' => now()->subDays(2),
        ]);
        $this->createNewsPost([
            'title' => 'Newer Post',
            'published_at' => now(),
        ]);

        $this->getJson('/api/news')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.title', 'Newer Post')
            ->assertJsonPath('data.items.data.1.title', 'Older Post');

        $this->getJson('/api/news?sort_by=oldest')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.title', 'Older Post')
            ->assertJsonPath('data.items.data.1.title', 'Newer Post');
    }

    public function test_news_are_filtered_by_category(): void {
        $fantasy = $this->createNewsCategory([
            'name' => 'Fantasy',
        ]);
        $cooking = $this->createNewsCategory([
            'name' => 'Cooking',
        ]);
        $this->createNewsPost([
            'title' => 'Fantasy Post',
            'category_id' => $fantasy->id,
        ]);
        $this->createNewsPost([
            'title' => 'Cooking Post',
            'category_id' => $cooking->id,
        ]);

        $this->getJson('/api/news?category=Fantasy')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items.data')
            ->assertJsonPath('data.items.data.0.title', 'Fantasy Post');
    }

    public function test_news_response_includes_active_categories(): void {
        $this->createNewsCategory([
            'name' => 'Fantasy',
        ]);
        $this->createNewsCategory([
            'name' => 'Inactive Category',
            'status' => 0,
        ]);

        $response = $this->getJson('/api/news')->assertSuccessful();
        $categories = collect($response->json('data.categories'));

        $this->assertTrue($categories->contains('Fantasy'));
        $this->assertFalse($categories->contains('Inactive Category'));
    }

    public function test_news_includes_seo_for_the_news_page(): void {
        $this->assertPageSeoIncluded('/api/news', 'news', 'News');
    }

    public function test_news_are_paginated_six_per_page(): void {
        for ($i = 1; $i <= 8; $i++) {
            $this->createNewsPost([
                'title' => "Post {$i}",
            ]);
        }

        $response = $this->getJson('/api/news')->assertSuccessful();

        $response->assertJsonCount(6, 'data.items.data');
        $response->assertJsonPath('data.items.pagination.total', 8);
        $response->assertJsonPath('data.items.pagination.last_page', 2);
    }
}

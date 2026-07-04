<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class NewsShowTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_news_show_returns_successful_response(): void
    {
        $post = $this->createNewsPost();

        $this->getJson("/api/news/{$post->slug}")->assertSuccessful();
    }

    public function test_news_show_returns_full_post_details(): void
    {
        $category = $this->createNewsCategory([
            'name' => 'Literature',
        ]);
        $post = $this->createNewsPost([
            'title' => 'A Great Post',
            'category_id' => $category->id,
            'excerpt' => 'Short excerpt.',
            'content' => 'Full body text.',
        ]);

        $this->getJson("/api/news/{$post->slug}")
            ->assertSuccessful()
            ->assertJsonPath('data.title', 'A Great Post')
            ->assertJsonPath('data.category', 'Literature')
            ->assertJsonPath('data.excerpt', 'Short excerpt.')
            ->assertJsonPath('data.content', 'Full body text.')
            ->assertJsonPath('data.seo.title', 'A Great Post');
    }

    public function test_news_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/news/does-not-exist')->assertNotFound();
    }

    public function test_news_show_returns_404_for_an_unpublished_post(): void
    {
        $post = $this->createNewsPost([
            'status' => 0,
        ]);

        $this->getJson("/api/news/{$post->slug}")->assertNotFound();
    }
}

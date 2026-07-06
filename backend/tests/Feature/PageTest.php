<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_page_returns_successful_response(): void {
        $this->createPage([
            'slug' => 'a-generic-page',
        ]);

        $this->getJson('/api/pages/a-generic-page')->assertSuccessful();
    }

    public function test_page_returns_null_data_for_unknown_slug(): void {
        $this->getJson('/api/pages/nonexistent')
            ->assertSuccessful()
            ->assertJsonPath('data', null);
    }

    public function test_page_returns_null_data_for_inactive_page(): void {
        $this->createPage([
            'slug' => 'hidden-page',
            'status' => 0,
        ]);

        $this->getJson('/api/pages/hidden-page')
            ->assertSuccessful()
            ->assertJsonPath('data', null);
    }

    public function test_page_includes_content_fields(): void {
        $this->createPage([
            'slug' => 'a-content-page',
            'title' => 'A Content Page',
            'content' => 'Full page content.',
            'excerpt' => 'Short excerpt.',
        ]);

        $this->getJson('/api/pages/a-content-page')
            ->assertSuccessful()
            ->assertJsonPath('data.title', 'A Content Page')
            ->assertJsonPath('data.content', 'Full page content.')
            ->assertJsonPath('data.excerpt', 'Short excerpt.');
    }

    public function test_page_seo_falls_back_to_title_and_excerpt_when_no_seo_meta_row(): void {
        $this->createPage([
            'slug' => 'no-seo-page',
            'title' => 'Fallback Title',
            'excerpt' => 'Fallback excerpt.',
        ]);

        $this->getJson('/api/pages/no-seo-page')
            ->assertSuccessful()
            ->assertJsonPath('data.seo.title', 'Fallback Title')
            ->assertJsonPath('data.seo.description', 'Fallback excerpt.');
    }

    public function test_page_seo_prefers_seo_meta_over_fallback(): void {
        $page = $this->createPage([
            'slug' => 'a-generic-page',
            'title' => 'A Generic Page',
            'excerpt' => 'Fallback excerpt.',
        ]);
        $this->createSeoMeta($page, [
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description.',
        ]);

        $this->getJson('/api/pages/a-generic-page')
            ->assertSuccessful()
            ->assertJsonPath('data.seo.title', 'Custom SEO Title')
            ->assertJsonPath('data.seo.description', 'Custom SEO description.');
    }
}

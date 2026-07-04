<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_contact_returns_successful_response(): void
    {
        $this->getJson('/api/pages/contact')->assertSuccessful();
    }

    public function test_contact_includes_active_faqs_in_order(): void
    {
        $this->createFaq([
            'title' => 'Second question',
            'content' => 'Second answer',
            'sort_order' => 2,
        ]);
        $this->createFaq([
            'title' => 'First question',
            'content' => 'First answer',
            'sort_order' => 1,
        ]);
        $this->createFaq([
            'title' => 'Hidden question',
            'status' => 0,
        ]);
        $this->createFaq([
            'title' => 'Other page question',
            'type' => 'about',
        ]);

        $this->getJson('/api/pages/contact')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.faqs')
            ->assertJsonPath('data.faqs.0.question', 'First question')
            ->assertJsonPath('data.faqs.0.answer', 'First answer')
            ->assertJsonPath('data.faqs.1.question', 'Second question');
    }

    public function test_contact_cache_is_invalidated_on_faq_write(): void
    {
        $faq = $this->createFaq([
            'title' => 'Original question',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/contact', $faq, 'data.faqs.0.question', 'Original question', [
            'title' => 'Updated question',
        ], 'Updated question');
    }

    public function test_contact_includes_seo_for_the_contact_page(): void
    {
        $this->assertPageSeoIncluded('/api/pages/contact', 'contact', 'Contact Us', 'Contact SEO Title');
    }

    public function test_contact_cache_is_invalidated_on_page_write(): void
    {
        $page = $this->createPage([
            'slug' => 'contact',
            'title' => 'Contact Us',
            'excerpt' => 'Original excerpt.',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/contact', $page, 'data.page.seo.description', 'Original excerpt.', [
            'excerpt' => 'Updated excerpt.',
        ], 'Updated excerpt.');
    }
}

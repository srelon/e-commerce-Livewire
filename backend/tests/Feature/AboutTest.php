<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class AboutTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_about_returns_successful_response(): void {
        $this->getJson('/api/pages/about')->assertSuccessful();
    }

    public function test_about_includes_active_team_members_in_order(): void {
        $this->createTeamMember([
            'name' => 'Second Member',
            'sort_order' => 2,
        ]);
        $this->createTeamMember([
            'name' => 'First Member',
            'sort_order' => 1,
        ]);
        $this->createTeamMember([
            'name' => 'Hidden Member',
            'status' => 0,
        ]);

        $this->getJson('/api/pages/about')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.team')
            ->assertJsonPath('data.team.0.name', 'First Member')
            ->assertJsonPath('data.team.1.name', 'Second Member');
    }

    public function test_about_includes_seo_for_the_about_page(): void {
        $this->assertPageSeoIncluded('/api/pages/about', 'about', 'About Us', 'About SEO Title');
    }

    public function test_about_cache_is_invalidated_on_team_member_write(): void {
        $member = $this->createTeamMember([
            'name' => 'Original Name',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/about', $member, 'data.team.0.name', 'Original Name', [
            'name' => 'Updated Name',
        ], 'Updated Name');
    }

    public function test_about_cache_is_invalidated_on_page_write(): void {
        $page = $this->createPage([
            'slug' => 'about',
            'title' => 'About Us',
            'excerpt' => 'Original excerpt.',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/about', $page, 'data.page.seo.description', 'Original excerpt.', [
            'excerpt' => 'Updated excerpt.',
        ], 'Updated excerpt.');
    }

    public function test_about_includes_active_perks_in_order(): void {
        $this->createPerk([
            'title' => 'Second Perk',
            'sort_order' => 2,
        ]);
        $this->createPerk([
            'title' => 'First Perk',
            'sort_order' => 1,
        ]);
        $this->createPerk([
            'title' => 'Hidden Perk',
            'status' => 0,
        ]);

        $this->getJson('/api/pages/about')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.perks')
            ->assertJsonPath('data.perks.0.title', 'First Perk')
            ->assertJsonPath('data.perks.1.title', 'Second Perk');
    }

    public function test_about_cache_is_invalidated_on_perk_write(): void {
        $perk = $this->createPerk([
            'title' => 'Original Perk',
        ]);

        $this->assertCacheInvalidatedOnWrite('/api/pages/about', $perk, 'data.perks.0.title', 'Original Perk', [
            'title' => 'Updated Perk',
        ], 'Updated Perk');
    }
}

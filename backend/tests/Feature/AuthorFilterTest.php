<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestDataHelper;
use Tests\TestCase;

class AuthorFilterTest extends TestCase
{
    use RefreshDatabase, TestDataHelper;

    public function test_authors_returns_successful_response(): void {
        $this->getJson('/api/authors')->assertSuccessful();
    }

    public function test_authors_include_their_book_count(): void {
        $author = $this->createAuthor([
            'name' => 'Prolific Author',
        ]);
        $this->createProduct(null, [
            'author_id' => $author->id,
        ]);
        $this->createProduct(null, [
            'author_id' => $author->id,
        ]);

        $response = $this->getJson('/api/authors')->assertSuccessful();
        $authors = collect($response->json('data.items.data'));

        $this->assertSame(2, $authors->firstWhere('name', 'Prolific Author')['books']);
    }

    public function test_authors_include_their_bestseller_count(): void {
        $author = $this->createAuthor([
            'name' => 'Bestselling Author',
        ]);
        $this->createProduct(null, [
            'author_id' => $author->id,
            'bestseller' => 1,
        ]);
        $this->createProduct(null, [
            'author_id' => $author->id,
            'bestseller' => 1,
        ]);
        $this->createProduct(null, [
            'author_id' => $author->id,
        ]);

        $response = $this->getJson('/api/authors')->assertSuccessful();
        $authors = collect($response->json('data.items.data'));

        $this->assertSame(2, $authors->firstWhere('name', 'Bestselling Author')['bestsellers']);
    }

    public function test_authors_are_sorted_by_books(): void {
        $prolific = $this->createAuthor([
            'name' => 'Prolific Author',
        ]);
        $sparse = $this->createAuthor([
            'name' => 'Sparse Author',
        ]);
        $this->createProduct(null, [
            'author_id' => $prolific->id,
        ]);
        $this->createProduct(null, [
            'author_id' => $prolific->id,
        ]);
        $this->createProduct(null, [
            'author_id' => $sparse->id,
        ]);

        $this->getJson('/api/authors?sort_by=books')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.name', 'Prolific Author');
    }

    public function test_authors_are_sorted_by_bestseller(): void {
        $bestseller_author = $this->createAuthor([
            'name' => 'Bestseller Author',
        ]);
        $regular_author = $this->createAuthor([
            'name' => 'Regular Author',
        ]);
        $this->createProduct(null, [
            'author_id' => $bestseller_author->id,
            'bestseller' => 1,
        ]);
        $this->createProduct(null, [
            'author_id' => $regular_author->id,
        ]);

        $this->getJson('/api/authors?sort_by=bestseller')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.name', 'Bestseller Author');
    }

    public function test_authors_are_sorted_by_date_added(): void {
        $older = $this->createAuthor([
            'name' => 'Older Author',
        ]);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->createAuthor([
            'name' => 'Newer Author',
        ]);

        $this->getJson('/api/authors')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.name', 'Newer Author');

        $this->getJson('/api/authors?sort_by=newest')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.name', 'Newer Author');

        $this->getJson('/api/authors?sort_by=oldest')
            ->assertSuccessful()
            ->assertJsonPath('data.items.data.0.name', 'Older Author');
    }

    public function test_authors_includes_seo_for_the_authors_page(): void {
        $this->assertPageSeoIncluded('/api/authors', 'authors', 'Authors');
    }

    public function test_authors_are_paginated(): void {
        for ($i = 1; $i <= 8; $i++) {
            $this->createAuthor([
                'name' => "Author {$i}",
            ]);
        }

        $response = $this->getJson('/api/authors')->assertSuccessful();

        $response->assertJsonCount(6, 'data.items.data');
        $response->assertJsonPath('data.items.pagination.total', 8);
        $response->assertJsonPath('data.items.pagination.last_page', 2);
    }
}

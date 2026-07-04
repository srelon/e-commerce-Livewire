<?php

namespace Tests\Helpers;

use App\Models\ContactInfo;
use App\Models\Faq;
use App\Models\Menu;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\Page;
use App\Models\Perk;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductsAuthor;
use App\Models\ProductsCategory;
use App\Models\ProductStock;
use App\Models\SeoMeta;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;

trait TestDataHelper
{
    protected function createCategory(array $overrides = []): ProductsCategory
    {
        return ProductsCategory::create(array_merge([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
            'icon' => [
                'original' => 'products_categories/test-icon.svg',
            ],
            'image' => [
                'original' => 'products_categories/test-promo.webp',
            ],
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createAuthor(array $overrides = []): ProductsAuthor
    {
        return ProductsAuthor::create(array_merge([
            'name' => 'Test Author ' . uniqid(),
            'slug' => 'test-author-' . uniqid(),
        ], $overrides));
    }

    protected function createProduct(?ProductsCategory $category = null, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => ($category ?? $this->createCategory())->id,
            'author_id' => $this->createAuthor()->id,
            'title' => 'Test Book ' . uniqid(),
            'slug' => 'test-book-' . uniqid(),
            'status' => 1,
        ], $overrides));
    }

    protected function createProductImage(Product $product, array $overrides = []): ProductImage
    {
        return ProductImage::create(array_merge([
            'product_id' => $product->id,
            'image' => [
                'original' => 'products/test-cover.webp',
            ],
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createProductStock(Product $product, array $overrides = []): ProductStock
    {
        return ProductStock::create(array_merge([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => '19.99',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createNewsCategory(array $overrides = []): NewsCategory
    {
        return NewsCategory::create(array_merge([
            'name' => 'Test News Category ' . uniqid(),
            'slug' => 'test-news-category-' . uniqid(),
            'status' => 1,
        ], $overrides));
    }

    protected function createNewsPost(array $overrides = []): NewsPost
    {
        return NewsPost::create(array_merge([
            'title' => 'Test Post ' . uniqid(),
            'slug' => 'test-post-' . uniqid(),
            'category_id' => $this->createNewsCategory()->id,
            'image' => [
                'original' => 'news/test-post.webp',
            ],
            'status' => 1,
            'published_at' => now(),
        ], $overrides));
    }

    protected function createMenuItem(array $overrides = []): Menu
    {
        return Menu::create(array_merge([
            'name' => 'Test Menu ' . uniqid(),
            'route' => 'home',
            'type' => 'route',
            'location' => 'header',
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createContact(array $overrides = []): ContactInfo
    {
        return ContactInfo::create(array_merge([
            'key' => 'test-' . uniqid(),
            'name' => 'Test Contact',
            'content' => 'test value',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createFaq(array $overrides = []): Faq
    {
        return Faq::create(array_merge([
            'title' => 'Test Question ' . uniqid(),
            'content' => 'Test answer.',
            'type' => 'contact',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createPage(array $overrides = []): Page
    {
        return Page::create(array_merge([
            'slug' => 'test-page-' . uniqid(),
            'title' => 'Test Page',
            'excerpt' => 'Test excerpt.',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createSeoMeta(Page $page, array $overrides = []): SeoMeta
    {
        return SeoMeta::create(array_merge([
            'type' => 'pages',
            'record_id' => $page->id,
            'seo_title' => 'Test SEO Title',
            'seo_description' => 'Test SEO description.',
            'seo_keywords' => 'test, keywords',
        ], $overrides));
    }

    protected function createTeamMember(array $overrides = []): TeamMember
    {
        return TeamMember::create(array_merge([
            'name' => 'Test Member ' . uniqid(),
            'role' => 'Test Role',
            'initials' => 'TM',
            'color' => '#000000',
            'bio' => 'Test bio.',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createPerk(array $overrides = []): Perk
    {
        return Perk::create(array_merge([
            'title' => 'Test Perk ' . uniqid(),
            'description' => 'Test perk description.',
            'icon' => 'M0 0h1v1H0z',
            'status' => 1,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function assertPageSeoIncluded(string $endpoint, string $slug, string $title, ?string $seo_title = null): void
    {
        $seo_title ??= "{$title} SEO Title";

        $page = $this->createPage([
            'slug' => $slug,
            'title' => $title,
        ]);
        $this->createSeoMeta($page, [
            'seo_title' => $seo_title,
        ]);

        $this->getJson($endpoint)
            ->assertSuccessful()
            ->assertJsonPath('data.page.seo.title', $seo_title);
    }

    protected function assertCacheInvalidatedOnWrite(string $endpoint, Model $model, string $json_path, string $before, array $update, string $after): void
    {
        $this->getJson($endpoint)->assertJsonPath($json_path, $before);

        $model->update($update);

        $this->getJson($endpoint)->assertJsonPath($json_path, $after);
    }
}

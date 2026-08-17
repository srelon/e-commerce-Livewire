<?php

namespace App\Livewire\Components\Catalog\Products;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HandlesFullPageSave;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasFormHelpers;
use App\Livewire\Traits\HasPublishableContentFields;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductsAuthor;
use App\Models\ProductsCategory;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use ConfirmsAction, HandlesFullPageSave, HasAccessControl, HasFormHelpers, HasPublishableContentFields, WithFileUploads;

    protected string $accessKey = 'products';

    protected string $routePrefix = 'products';

    protected string $routeParamKey = 'product';

    protected string $saveMessageName = 'Product';

    public ?Product $product = null;

    public string $short_description = '';

    public string $description = '';

    public $bestseller = 0;

    public $status = 1;

    public array $new_gallery_files = [];

    public function mount(?Product $product = null): void {
        if ($product) {
            $this->guardView();

            $this->product = $product;
            $this->fillTitleSlug($product->title, $product->slug);
            $this->short_description = (string) $product->short_description;
            $this->description = (string) $product->description;
            $this->category_id = $product->category_id;
            $this->author_id = $product->author_id;
            $this->bestseller = $product->bestseller;
            $this->status = $product->status;
            $this->fillPublishedAt($product->published_at);
            $this->fillSeoFields($product->seo);
        } else {
            abort_unless($this->hasAccess('edit'), 403);

            $this->category_id = $this->categories->first()?->id;
            $this->author_id = $this->authors->first()?->id;
        }
    }

    public function updatedNewGalleryFiles(): void {
        if (! $this->product) {
            return;
        }

        $this->storeGalleryFiles($this->product);
        $this->new_gallery_files = [];
        $this->dispatchGalleryUpdate();
    }

    #[On('deleteGalleryImage')]
    public function deleteGalleryImage(int $imageId): void {
        if (! $this->guardSave() || ! $this->product) {
            return;
        }

        $this->product->images()->whereKey($imageId)->delete();
        CacheService::flush('product');
        $this->dispatchGalleryUpdate();
        $this->dispatch('notify', type: 'success', message: 'Image deleted.');
    }

    public function saveOrder(array $tree): void {
        if (! $this->guardSave() || ! $this->product) {
            return;
        }

        ProductImage::bulkUpdateAndFlush($tree, fn ($node, $index) => ['sort_order' => $index]);

        $this->dispatchGalleryUpdate();
        $this->dispatch('notify', type: 'success', message: 'Image order saved.');
    }

    private function storeGalleryFiles(Product $product): void {
        $nextSortOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($this->new_gallery_files as $file) {
            $product->images()->create([
                'image' => ['original' => $file->store('products', 'public')],
                'sort_order' => $nextSortOrder++,
            ]);
        }
    }

    private function dispatchGalleryUpdate(): void {
        $this->dispatch('gallery-updated', field: 'new_gallery_files', images: $this->galleryImages());
    }

    private function galleryImages(): array {
        if (! $this->product) {
            return [];
        }

        return $this->product->images()->get()
            ->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => $this->previewUrl($image->image),
            ])
            ->all();
    }

    #[Computed]
    public function categories(): Collection {
        return ProductsCategory::orderBy('name')->get();
    }

    #[Computed]
    public function authors(): Collection {
        return ProductsAuthor::orderBy('name')->get();
    }

    #[Computed]
    public function schema(): array {
        return [
            [
                'name' => 'new_gallery_files',
                'label' => 'Gallery',
                'type' => 'gallery',
                'full_width' => true,
                'images' => $this->galleryImages(),
            ],
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
            ],
            [
                'name' => 'slug',
                'label' => 'Slug',
                'type' => 'text',
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'options' => $this->categories->map(fn (ProductsCategory $category) => [
                    'value' => $category->id,
                    'label' => $category->name,
                ])->all(),
            ],
            [
                'name' => 'author_id',
                'label' => 'Author',
                'type' => 'select',
                'options' => $this->authors->map(fn (ProductsAuthor $author) => [
                    'value' => $author->id,
                    'label' => $author->name,
                ])->all(),
            ],
            [
                'name' => 'bestseller',
                'label' => 'Bestseller',
                'type' => 'select',
                'options' => [
                    ['value' => 1, 'label' => 'Yes'],
                    ['value' => 0, 'label' => 'No'],
                ],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    ['value' => 0, 'label' => 'Created'],
                    ['value' => 1, 'label' => 'Active'],
                    ['value' => 2, 'label' => 'Archived'],
                    ['value' => 3, 'label' => 'Reserved'],
                ],
            ],
            [
                'name' => 'published_at',
                'label' => 'Published at',
                'type' => 'text',
                'input_type' => 'datetime-local',
            ],
            [
                'name' => 'short_description',
                'label' => 'Short description',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 2,
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 8,
            ],
            [
                'name' => 'seo_title',
                'label' => 'SEO title',
                'type' => 'text',
                'placeholder' => $this->title ?: null,
            ],
            [
                'name' => 'seo_keywords',
                'label' => 'SEO keywords',
                'type' => 'text',
                'placeholder' => 'comma, separated, keywords',
            ],
            [
                'name' => 'seo_description',
                'label' => 'SEO description',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 2,
                'placeholder' => $this->short_description ?: null,
            ],
        ];
    }

    private function persist(): ?Product {
        if (! $this->guardSave()) {
            return null;
        }

        $this->validate([
            'category_id' => ['required', 'exists:products_categories,id'],
            'author_id' => ['required', 'exists:products_authors,id'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'bestseller' => ['required', 'in:0,1'],
            'status' => ['required', 'in:0,1,2,3'],
            'new_gallery_files.*' => ['nullable', 'image', 'max:4096'],
            ...$this->slugValidationRule('products', $this->product?->id),
            ...$this->publishedAtValidationRule(),
            ...$this->seoValidationRules(),
        ]);

        $product = $this->product ?? new Product;
        $product->category_id = (int) $this->category_id;
        $product->author_id = (int) $this->author_id;
        $product->title = $this->title;
        $product->slug = $this->slug;
        $product->short_description = $this->short_description;
        $product->description = $this->description;
        $product->bestseller = (int) $this->bestseller;
        $product->status = (int) $this->status;
        $product->published_at = $this->parsePublishedAt();
        $product->save();

        $this->storeGalleryFiles($product);
        $this->new_gallery_files = [];

        $this->saveSeoFields($product);

        $this->product = $product;

        return $product;
    }

    public function render() {
        return $this->renderResourceForm(
            pageTitle: $this->product ? 'Edit product' : 'Create product',
            disabled: ! $this->hasAccess('edit'),
            cancelRoute: 'admin.products.index',
            siteUrl: $this->product ? rtrim(config('app.frontend_url'), '/')."/product/{$this->product->slug}" : null,
            view: 'livewire.admin.catalog.products.form',
            viewData: ['product' => $this->product],
        );
    }
}

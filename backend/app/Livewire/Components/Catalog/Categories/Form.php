<?php

namespace App\Livewire\Components\Catalog\Categories;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HandlesModalForm;
use App\Livewire\Traits\HasAccessControl;
use App\Models\ProductsCategory;
use App\Services\CacheService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use ConfirmsAction, HandlesModalForm, HasAccessControl, WithFileUploads;

    protected string $accessKey = 'categories';

    public bool $show_modal = false;

    public ?int $editing_id = null;

    public string $name = '';

    public $status = 1;

    public $image = null;

    public $icon = null;

    #[Computed]
    public function tree(): array {
        return ProductsCategory::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ProductsCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'status' => (int) $category->status,
                'products_count' => $category->products_count,
                'image_url' => $this->previewUrl($category->image),
            ])
            ->all();
    }

    #[Computed]
    public function schema(): array {
        $category = $this->editing_id ? ProductsCategory::find($this->editing_id) : null;

        return [
            [
                'name' => 'image',
                'label' => 'Image',
                'type' => 'file',
                'preview' => $this->previewUrl($category?->image),
                'preview_class' => 'h-20 w-20 rounded object-cover',
            ],
            [
                'name' => 'icon',
                'label' => 'Icon',
                'type' => 'file',
                'preview' => $this->previewUrl($category?->icon),
                'preview_class' => 'h-20 w-20 rounded object-cover',
            ],
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            $this->statusField(),
        ];
    }

    public function openEdit(int $id): void {
        $category = ProductsCategory::findOrFail($id);

        $this->resetForm();
        $this->editing_id = $category->id;
        $this->name = $category->name;
        $this->status = (int) $category->status;
        $this->show_modal = true;
    }

    protected function resetForm(): void {
        $this->resetFormFields(['editing_id', 'name', 'status', 'image', 'icon']);
    }

    public function save(): void {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
            'image' => ['nullable', 'image', 'max:4096'],
            'icon' => ['nullable', 'image', 'max:2048'],
        ]);

        $isCreating = ! $this->editing_id;
        $category = $this->editing_id ? ProductsCategory::findOrFail($this->editing_id) : new ProductsCategory;
        $category->name = $this->name;
        $category->status = (int) $this->status;

        if ($isCreating) {
            $category->sort_order = (int) ProductsCategory::max('sort_order') + 1;
        }

        if ($this->image) {
            $category->image = ['original' => $this->image->store('products_categories', 'public')];
        }

        if ($this->icon) {
            $category->icon = ['original' => $this->icon->store('products_categories', 'public')];
        }

        $category->save();

        $this->show_modal = false;

        $this->dispatchTreeUpdated();
        $this->dispatch('notify', type: 'success', message: $isCreating ? 'Category created.' : 'Category updated.');
    }

    #[On('deleteCategory')]
    public function deleteCategory(int $categoryId): void {
        if (! $this->guardSave()) {
            return;
        }

        ProductsCategory::whereKey($categoryId)->delete();
        CacheService::flush('product');

        $this->dispatchTreeUpdated();
        $this->dispatch('notify', type: 'success', message: 'Category deleted.');
    }

    public function saveOrder(array $tree): void {
        if (! $this->guardSave()) {
            return;
        }

        ProductsCategory::bulkUpdateAndFlush($tree, fn ($node, $index) => ['sort_order' => $index]);

        $this->dispatchTreeUpdated();
        $this->dispatch('notify', type: 'success', message: 'Category order saved.');
    }

    private function dispatchTreeUpdated(): void {
        unset($this->tree);
        $this->dispatch('categories-tree-updated', tree: $this->tree);
    }

    public function render() {
        return view('livewire.admin.catalog.categories.index');
    }
}

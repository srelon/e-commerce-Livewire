<?php

namespace App\Livewire\Components\Menu;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Models\Menu;
use App\Models\NewsPost;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.admin.app')]
class Tree extends Component
{
    use ConfirmsAction, HasAccessControl;

    protected string $accessKey = 'menus';

    // Must stay in sync with the route names in frontend/src/routes/router.ts
    protected const ROUTE_OPTIONS = [
        'home' => 'Home',
        'products' => 'Products (list)',
        'product' => 'Product (single)',
        'authors' => 'Authors',
        'about' => 'About Us',
        'contact' => 'Contact Us',
        'news' => 'News (list)',
        'post' => 'News post (single)',
        'cart' => 'Cart',
    ];

    protected const ROUTE_SLUG_MODELS = [
        'product' => Product::class,
        'post' => NewsPost::class,
    ];

    public bool $show_modal = false;

    public ?int $editing_id = null;

    public ?int $parent_id = null;

    public string $name = '';

    public string $type = 'route';

    public string $route = '';

    public ?string $params_slug = null;

    public string $active_location = 'header';

    public string $slug_search = '';

    public array $slug_results = [];

    public function mount(): void
    {
        $this->guardView();
    }

    public function getRouteOptions(): array
    {
        return self::ROUTE_OPTIONS;
    }

    #[Computed]
    public function tree(): array
    {
        $all = Menu::where('location', $this->active_location)->orderBy('sort_order')->get();
        $byParent = $all->groupBy('parent_id');

        $map = function (int $parentId) use (&$map, $byParent): array {
            return $byParent->get($parentId, collect())
                ->map(fn (Menu $node) => [
                    'id' => $node->id,
                    'name' => $node->name,
                    'type' => $node->type,
                    'route' => $node->route,
                    'route_label' => $node->type === 'route' ? (self::ROUTE_OPTIONS[$node->route] ?? $node->route) : null,
                    'children' => $map($node->id),
                ])
                ->values()
                ->all();
        };

        return $map(-1);
    }

    public function updatedActiveLocation(): void
    {
        unset($this->tree);
        $this->dispatch('menu-tree-updated', tree: $this->tree);
    }

    public function openCreate(?int $parentId = null): void
    {
        if (! $this->hasAccess('edit')) {
            return;
        }

        $this->resetForm();
        $this->parent_id = $parentId;
        $this->show_modal = true;
    }

    public function openEdit(int $id): void
    {
        $node = Menu::findOrFail($id);

        $this->resetForm();
        $this->editing_id = $node->id;
        $this->parent_id = $node->parent_id;
        $this->name = $node->name;
        $this->type = $node->type;
        $this->route = (string) $node->route;
        $this->params_slug = $node->params['slug'] ?? null;

        if ($this->params_slug && array_key_exists($this->route, self::ROUTE_SLUG_MODELS)) {
            $model = self::ROUTE_SLUG_MODELS[$this->route];
            $this->slug_search = (string) $model::query()->where('slug', $this->params_slug)->value('title');
        }

        $this->show_modal = true;
    }

    protected function resetForm(): void
    {
        $this->editing_id = null;
        $this->parent_id = null;
        $this->name = '';
        $this->type = 'route';
        $this->route = '';
        $this->params_slug = null;
        $this->slug_search = '';
        $this->slug_results = [];
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->show_modal = false;
    }

    public function updatedType(): void
    {
        $this->route = '';
        $this->params_slug = null;
        $this->slug_search = '';
        $this->slug_results = [];
    }

    public function updatedRoute(): void
    {
        $this->params_slug = null;
        $this->slug_results = [];
    }

    public function updatedSlugSearch(string $value): void
    {
        $model = self::ROUTE_SLUG_MODELS[$this->route] ?? null;

        if (! $model) {
            $this->slug_results = [];

            return;
        }

        $this->slug_results = $model::query()
            ->when($value !== '', fn ($query) => $query->where('title', 'like', "%{$value}%"))
            ->latest('id')
            ->limit(10)
            ->get(['title', 'slug'])
            ->map(fn ($row) => ['slug' => $row->slug, 'title' => $row->title])
            ->all();
    }

    public function selectSlug(string $slug, string $title): void
    {
        $this->params_slug = $slug;
        $this->slug_search = $title;
        $this->slug_results = [];
    }

    public function save(): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:link,route'],
            'route' => ['required', 'string', 'max:255'],
            'params_slug' => [
                Rule::requiredIf($this->type === 'route' && array_key_exists($this->route, self::ROUTE_SLUG_MODELS)),
            ],
        ], [
            'params_slug.required' => 'Select a target for this route.',
        ]);

        $node = $this->editing_id ? Menu::findOrFail($this->editing_id) : new Menu();
        $node->name = $this->name;
        $node->type = $this->type;
        $node->route = $this->route;

        $params = [];

        if ($this->type === 'route' && array_key_exists($this->route, self::ROUTE_SLUG_MODELS) && filled($this->params_slug)) {
            $params['slug'] = $this->params_slug;
        }

        $node->params = filled($params) ? $params : null;

        if (! $this->editing_id) {
            $node->parent_id = $this->parent_id ?? -1;
            $node->location = $this->parent_id ? Menu::findOrFail($this->parent_id)->location : $this->active_location;
            $node->sort_order = (int) Menu::where('parent_id', $node->parent_id)->max('sort_order') + 1;
        }

        $node->save();

        $isCreating = ! $this->editing_id;
        $this->show_modal = false;

        unset($this->tree);
        $this->dispatch('menu-tree-updated', tree: $this->tree);
        $this->dispatch('notify', type: 'success', message: $isCreating ? 'Menu item created.' : 'Menu item updated.');
    }

    #[On('deleteMenuItem')]
    public function delete(int $id): void
    {
        if (! $this->guardSave()) {
            return;
        }

        Menu::where('parent_id', $id)->update(['parent_id' => -1]);
        Menu::find($id)?->delete();

        unset($this->tree);
        $this->dispatch('menu-tree-updated', tree: $this->tree);
        $this->dispatch('notify', type: 'success', message: 'Menu item deleted.');
    }

    public function saveOrder(array $tree): void
    {
        if (! $this->guardSave()) {
            return;
        }

        $this->persistOrder($tree, -1);

        unset($this->tree);
        $this->dispatch('menu-tree-updated', tree: $this->tree);
        $this->dispatch('notify', type: 'success', message: 'Menu order saved.');
    }

    protected function persistOrder(array $nodes, int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            $model = Menu::find((int) $node['id']);
            $model?->update([
                'parent_id' => $parentId,
                'sort_order' => $index,
            ]);

            if (empty($node['children'])) {
                continue;
            }

            // Menus only support one level of nesting. If this node itself just became
            // a child (parentId !== -1), its own children can't stay nested any deeper —
            // promote them back to the root instead of silently persisting an invisible
            // third level (the tree view only ever renders two levels deep).
            $this->persistOrder($node['children'], $parentId === -1 ? (int) $node['id'] : -1);
        }
    }

    public function render()
    {
        return view('livewire.admin.menu.tree');
    }
}

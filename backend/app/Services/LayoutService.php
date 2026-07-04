<?php

namespace App\Services;

use App\Models\ContactInfo;
use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LayoutService
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function getLayout(): array
    {
        return Cache::tags([CacheService::TAG_LAYOUT])->remember(
            'layout.data',
            CacheService::TTL_LAYOUT,
            fn () => [
                'categories' => $this->productService->getCategories()->toArray(),
                'menu' => $this->getMenu()->toArray(),
                'contacts' => $this->getContacts()->toArray(),
                'best_books' => $this->productService->getBestsellers(5)->toArray(),
            ],
        );
    }

    protected function getMenu(): Collection
    {
        return Menu::query()
            ->where('parent_id', -1)
            ->where('location', 'header')
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Menu $item) => $this->formatMenuItem($item));
    }

    protected function formatMenuItem(Menu $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'type' => $item->type,
            'route' => $item->route,
            'params' => $item->params,
            'children' => $item->children->map(fn (Menu $child) => $this->formatMenuItem($child))->all(),
        ];
    }

    protected function getContacts(): Collection
    {
        return ContactInfo::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get(['key', 'name', 'content', 'icon']);
    }
}

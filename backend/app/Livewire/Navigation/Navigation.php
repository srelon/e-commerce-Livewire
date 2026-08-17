<?php

namespace App\Livewire\Navigation;

use App\Models\Admin;
use Illuminate\Support\Facades\Route;

class Navigation
{
    protected static array $items = [
        [
            'label' => 'Dashboard',
            'icon' => 'squares-2x2',
            'route' => 'admin.dashboard',
            'route_pattern' => 'admin.dashboard',
        ],
        [
            'label' => 'Users',
            'icon' => 'users',
            'route' => 'admin.users.index',
            'route_pattern' => 'admin.users.*',
            'access' => 'users.view',
        ],
        [
            'label' => 'Menus',
            'icon' => 'bars-3',
            'route' => 'admin.menus.index',
            'route_pattern' => 'admin.menus.*',
            'access' => 'menus.view',
        ],
        [
            'group' => 'Catalog',
            'icon' => 'shopping-bag',
            'items' => [
                [
                    'label' => 'Products',
                    'route' => 'admin.products.index',
                    'route_pattern' => 'admin.products.*',
                    'access' => 'products.view',
                ],
                [
                    'label' => 'Categories',
                    'route' => 'admin.categories.index',
                    'route_pattern' => 'admin.categories.*',
                    'access' => 'categories.view',
                ],
                [
                    'label' => 'Authors',
                    'route' => 'admin.authors.index',
                    'route_pattern' => 'admin.authors.*',
                    'access' => 'authors.view',
                ],
            ],
        ],
        [
            'group' => 'Content',
            'icon' => 'newspaper',
            'items' => [
                [
                    'label' => 'News',
                    'route' => 'admin.news.index',
                    'route_pattern' => 'admin.news.*',
                    'access' => 'news.view',
                ],
            ],
        ],
        [
            'group' => 'Administration',
            'icon' => 'cog-6-tooth',
            'items' => [
                [
                    'label' => 'Admins',
                    'route' => 'admin.admins.index',
                    'route_pattern' => 'admin.admins.*',
                    'access' => 'admins.view',
                ],
                [
                    'label' => 'Roles',
                    'route' => 'admin.roles.index',
                    'route_pattern' => 'admin.roles.*',
                    'access' => 'roles.view',
                ],
            ],
        ],
    ];

    public static function visible(Admin $admin): array {
        return collect(self::$items)
            ->map(function (array $entry) use ($admin) {
                if (isset($entry['items'])) {
                    $entry['items'] = self::filterItems($entry['items'], $admin);

                    return $entry['items'] === [] ? null : $entry;
                }

                return self::itemVisible($entry, $admin) ? $entry : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected static function filterItems(array $items, Admin $admin): array {
        return collect($items)->filter(fn (array $item) => self::itemVisible($item, $admin))->values()->all();
    }

    protected static function itemVisible(array $item, Admin $admin): bool {
        if (! Route::has($item['route'])) {
            return false;
        }

        return ! isset($item['access']) || $admin->hasAccess($item['access']);
    }
}

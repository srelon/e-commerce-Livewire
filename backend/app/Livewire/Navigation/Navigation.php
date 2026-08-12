<?php

namespace App\Livewire\Navigation;

use App\Models\Admin;
use Illuminate\Support\Facades\Route;

class Navigation
{
    protected static array $items = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'route_pattern' => 'admin.dashboard',
        ],
        [
            'label' => 'Users',
            'route' => 'admin.users.index',
            'route_pattern' => 'admin.users.*',
            'access' => 'users.view',
        ],
        [
            'label' => 'Menus',
            'route' => 'admin.menus.index',
            'route_pattern' => 'admin.menus.*',
            'access' => 'menus.view',
        ],
        [
            'group' => 'Settings',
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

    public static function visible(Admin $admin): array
    {
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

    protected static function filterItems(array $items, Admin $admin): array
    {
        return collect($items)->filter(fn (array $item) => self::itemVisible($item, $admin))->values()->all();
    }

    protected static function itemVisible(array $item, Admin $admin): bool
    {
        if (! Route::has($item['route'])) {
            return false;
        }

        return ! isset($item['access']) || $admin->hasAccess($item['access']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Home',
                'route' => 'home',
            ],
            [
                'name' => 'Products',
                'route' => 'products',
            ],
            [
                'name' => 'Authors',
                'route' => 'authors',
            ],
            [
                'name' => 'About Us',
                'route' => 'about',
            ],
            [
                'name' => 'News',
                'route' => 'news',
            ],
            [
                'name' => 'Contact Us',
                'route' => 'contact',
            ],
        ];

        foreach ($items as $sort => $item) {
            Menu::firstOrCreate(
                [
                    'name' => $item['name'],
                    'location' => 'header',
                ],
                [
                    'route' => $item['route'],
                    'type' => 'route',
                    'sort_order' => $sort + 1,
                ],
            );
        }
    }
}

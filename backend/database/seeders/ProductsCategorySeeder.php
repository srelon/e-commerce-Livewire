<?php

namespace Database\Seeders;

use App\Models\ProductsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Art & Design',
                'icon' => 'category-icon-1.svg',
                'promo' => 'promo-art-design.jpg',
            ],
            [
                'name' => 'History',
                'icon' => 'category-icon-2.svg',
                'promo' => 'promo-history.jpg',
            ],
            [
                'name' => 'Science',
                'icon' => 'category-icon-3.svg',
                'promo' => 'promo-science.jpg',
            ],
            [
                'name' => 'Novels',
                'icon' => 'category-icon-4.svg',
                'promo' => 'promo-novels.jpg',
            ],
            [
                'name' => 'Cooking',
                'icon' => 'category-icon-5.svg',
                'promo' => 'promo-cooking.jpg',
            ],
            [
                'name' => 'Self-help',
                'icon' => 'category-icon-6.svg',
                'promo' => 'promo-self-help.jpg',
            ],
            [
                'name' => 'Adventure',
                'icon' => 'category-icon-7.svg',
                'promo' => 'promo-adventure.jpg',
            ],
            [
                'name' => 'Fantasy',
                'icon' => 'category-icon-8.svg',
                'promo' => 'promo-fantasy.jpg',
            ],
            [
                'name' => 'Romance',
                'icon' => 'category-icon-9.svg',
                'promo' => 'promo-romance.jpg',
            ],
            [
                'name' => 'Business',
                'icon' => 'category-icon-10.svg',
                'promo' => 'promo-business.jpg',
            ],
        ];

        foreach ($categories as $sort => $category) {
            ProductsCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => [
                        'original' => "products_categories/{$category['icon']}",
                    ],
                    'image' => [
                        'original' => "products_categories/{$category['promo']}",
                    ],
                    'sort_order' => $sort + 1,
                    'status' => 1,
                ],
            );
        }
    }
}

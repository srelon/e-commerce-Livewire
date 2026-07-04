<?php

namespace Database\Seeders;

use App\Models\Perk;
use Illuminate\Database\Seeder;

class PerkSeeder extends Seeder
{
    public function run(): void
    {
        $perks = [
            [
                'title' => 'Free Delivery',
                'description' => 'On all orders over $40',
                'icon' => 'M1 1h3l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L22 6H6M16 19a1 1 0 1 1-2 0 1 1 0 0 1 2 0M9 19a1 1 0 1 1-2 0 1 1 0 0 1 2 0',
            ],
            [
                'title' => '24/7 Support',
                'description' => 'Round-the-clock assistance',
                'icon' => 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z',
            ],
            [
                'title' => 'Easy Returns',
                'description' => '30-day hassle-free returns',
                'icon' => 'M1 4v6h6M23 20v-6h-6M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4-4.64 4.36A9 9 0 0 1 3.51 15',
            ],
            [
                'title' => 'Secure Checkout',
                'description' => 'SSL encrypted payments',
                'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
            ],
        ];

        foreach ($perks as $index => $data) {
            Perk::firstOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'status' => 1,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\DeliveryService;
use Illuminate\Database\Seeder;

class DeliveryServiceSeeder extends Seeder
{
    public function run(): void {
        $services = [
            [
                'name' => 'Pickup',
                'key' => 'pickup',
            ],
            [
                'name' => 'Nova Poshta',
                'key' => 'nova_poshta',
            ],
        ];

        foreach ($services as $service) {
            DeliveryService::firstOrCreate(
                ['key' => $service['key']],
                [
                    'name' => $service['name'],
                    'status' => 1,
                ],
            );
        }
    }
}

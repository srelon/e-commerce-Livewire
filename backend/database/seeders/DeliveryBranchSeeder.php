<?php

namespace Database\Seeders;

use App\Models\DeliveryBranch;
use App\Models\DeliveryService;
use Illuminate\Database\Seeder;

class DeliveryBranchSeeder extends Seeder
{
    public function run(): void {
        $nova_poshta = DeliveryService::where('key', 'nova_poshta')->firstOrFail();

        $branches_by_city = [
            'Kyiv' => [
                'Branch #1, Khreshchatyk St, 22',
                'Branch #5, Peremohy Ave, 10',
                'Branch #12, Lisova St, 4',
            ],
            'Lviv' => [
                'Branch #2, Svobody Ave, 15',
                'Branch #7, Horodotska St, 33',
            ],
            'Odesa' => [
                'Branch #3, Derybasivska St, 8',
                'Branch #9, Shevchenko Ave, 21',
            ],
            'Kharkiv' => [
                'Branch #4, Svobody Sq, 5',
                'Branch #11, Sumska St, 40',
            ],
            'Dnipro' => [
                'Branch #6, Yavornytskoho Ave, 18',
            ],
        ];

        foreach ($branches_by_city as $city => $branches) {
            foreach ($branches as $branch) {
                $record = DeliveryBranch::withTrashed()->firstOrCreate(
                    [
                        'delivery_id' => $nova_poshta->id,
                        'city' => $city,
                        'branch' => $branch,
                    ],
                    ['status' => 1],
                );

                if ($record->trashed()) {
                    $record->restore();
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\ContactInfo;
use Illuminate\Database\Seeder;

class ContactInfoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'key' => 'address',
                'name' => 'Address',
                'content' => '27 Division St, New York, NY 10002',
                'icon' => 'M7.5 0A5.5 5.5 0 0 0 2 5.5C2 9.358 7.5 15 7.5 15S13 9.358 13 5.5A5.5 5.5 0 0 0 7.5 0m0 7.5a2 2 0 1 1 2-2 2 2 0 0 1-2 2',
            ],
            [
                'key' => 'working_hours',
                'name' => 'Working Hours',
                'content' => 'Mon–Sat: 9:00AM – 6:00PM',
                'icon' => 'M7.5.5A6.5 6.5 0 1 0 14 7 6.508 6.508 0 0 0 7.5.5m0 11.75A5.25 5.25 0 1 1 12.75 7 5.256 5.256 0 0 1 7.5 12.25M8.125 7V4.25a.625.625 0 0 0-1.25 0V7.5a.625.625 0 0 0 .625.625H10a.625.625 0 0 0 0-1.25z',
            ],
            [
                'key' => 'email',
                'name' => 'Email',
                'content' => 'hello@example.com',
                'icon' => 'M13.5 2h-12A1.5 1.5 0 0 0 0 3.5v8A1.5 1.5 0 0 0 1.5 13h12a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 13.5 2m0 1 .09.007L7.5 7.572 1.41 3.007A.5.5 0 0 1 1.5 3zM1 11.5v-7.8l6.22 4.573a.5.5 0 0 0 .56 0L14 3.7v7.8a.5.5 0 0 1-.5.5h-12a.5.5 0 0 1-.5-.5',
            ],
            [
                'key' => 'phone',
                'name' => 'Phone',
                'content' => '578-393-4937',
                'icon' => 'M10.628 10.628a5 5 0 1 1-6.257-6.257 5 5 0 0 1 6.257 6.257m.707.707A6 6 0 0 1 4.92 4.92a6 6 0 0 1 6.415 6.415M13.5 14.207l-2.854-2.854.707-.707L14.207 13.5z',
            ],
        ];

        foreach ($items as $sort => $item) {
            ContactInfo::firstOrCreate(
                ['key' => $item['key']],
                [
                    'name' => $item['name'],
                    'content' => $item['content'],
                    'icon' => $item['icon'],
                    'status' => 1,
                    'sort_order' => $sort + 1,
                ],
            );
        }
    }
}

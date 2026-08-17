<?php

namespace Database\Seeders;

use App\Models\NewsAuthor;
use Illuminate\Database\Seeder;

class NewsAuthorSeeder extends Seeder
{
    public function run(): void {
        $authors = [
            ['name' => 'Grace Lindqvist'],
            ['name' => 'Samuel Rourke'],
            ['name' => 'Priya Chandrasekaran'],
        ];

        foreach ($authors as $author) {
            $record = NewsAuthor::withTrashed()->firstOrCreate(
                ['name' => $author['name']],
                [
                    'public_id' => (string) random_int(10000000, 99999999),
                    'name' => $author['name'],
                    'status' => 1,
                ],
            );

            if ($record->trashed()) {
                $record->restore();
            }
        }
    }
}

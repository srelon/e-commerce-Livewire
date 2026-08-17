<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void {
        $categories = ['Literature', 'Cultural', 'Reading', 'Authors'];

        foreach ($categories as $name) {
            $category = NewsCategory::withTrashed()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'status' => 1,
                ],
            );

            if ($category->trashed()) {
                $category->restore();
            }
        }
    }
}

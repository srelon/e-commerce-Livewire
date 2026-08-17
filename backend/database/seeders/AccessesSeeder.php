<?php

namespace Database\Seeders;

use App\Models\AdminAccess;
use Illuminate\Database\Seeder;

class AccessesSeeder extends Seeder
{
    public function run(): void {
        $accesses = [
            [
                'key' => 'users',
                'title' => 'Users',
            ],
            [
                'key' => 'admins',
                'title' => 'Admins',
            ],
            [
                'key' => 'roles',
                'title' => 'Roles',
            ],
            [
                'key' => 'menus',
                'title' => 'Menus',
            ],
            [
                'key' => 'products',
                'title' => 'Products',
            ],
            [
                'key' => 'categories',
                'title' => 'Categories',
            ],
            [
                'key' => 'authors',
                'title' => 'Authors',
            ],
            [
                'key' => 'news',
                'title' => 'News',
            ],
            [
                'key' => 'reviews',
                'title' => 'Reviews',
            ],
        ];

        foreach ($accesses as $access) {
            AdminAccess::firstOrCreate(['key' => $access['key']], $access);
        }
    }
}

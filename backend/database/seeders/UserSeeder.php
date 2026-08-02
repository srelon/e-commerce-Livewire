<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void {
        User::firstOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'public_id' => (string) random_int(10000000, 99999999),
                'name' => 'Test User',
                'password' => '123456789',
            ],
        );
    }
}

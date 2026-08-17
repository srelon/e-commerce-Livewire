<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void {
        $test_user = User::firstOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'public_id' => (string) random_int(10000000, 99999999),
                'name' => 'Test User',
                'password' => '123456789',
            ],
        );

        User::factory()->count(30)->create();

        $test_user->is_moderator = true;
        $test_user->save();

        $superadmin = Admin::where('email', 'admin@admin.com')->first();

        if ($superadmin) {
            $superadmin->moderators()->syncWithoutDetaching([$test_user->id]);
        }
    }
}

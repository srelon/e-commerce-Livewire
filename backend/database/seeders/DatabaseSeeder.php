<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            AccessesSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            ProductsCategorySeeder::class,
            ProductsAuthorSeeder::class,
            ProductSeeder::class,
            NewsCategorySeeder::class,
            NewsPostSeeder::class,
            DeliveryServiceSeeder::class,
            DeliveryBranchSeeder::class,
            PaymentSeeder::class,
            MenuSeeder::class,
            ContactInfoSeeder::class,
            FaqSeeder::class,
            PageSeeder::class,
            TeamMemberSeeder::class,
            PerkSeeder::class,
        ]);

        // User::factory(10)->create();
    }
}

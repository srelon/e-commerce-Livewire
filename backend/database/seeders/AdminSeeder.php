<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminAccess;
use App\Models\AdminRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void {
        $superadmin_role = AdminRole::firstOrCreate(
            ['name' => 'superadmin'],
            ['label' => 'Super Admin'],
        );

        $admin_role = AdminRole::firstOrCreate(
            ['name' => 'admin'],
            ['label' => 'Admin'],
        );

        $all_accesses = AdminAccess::all();
        $users_access = AdminAccess::where('key', 'users')->first();

        DB::table('admin_role_access')->where('role_id', $superadmin_role->id)->delete();
        foreach ($all_accesses as $access) {
            DB::table('admin_role_access')->insert([
                [
                    'role_id' => $superadmin_role->id,
                    'access_id' => $access->id,
                    'type' => 1,
                ],
                [
                    'role_id' => $superadmin_role->id,
                    'access_id' => $access->id,
                    'type' => 2,
                ],
            ]);
        }

        DB::table('admin_role_access')->where('role_id', $admin_role->id)->delete();
        if ($users_access) {
            DB::table('admin_role_access')->insert([
                [
                    'role_id' => $admin_role->id,
                    'access_id' => $users_access->id,
                    'type' => 1,
                ],
            ]);
        }

        Admin::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => '123456789',
                'role_id' => $superadmin_role->id,
            ],
        );
    }
}

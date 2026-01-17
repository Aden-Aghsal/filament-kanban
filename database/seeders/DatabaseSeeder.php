<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Panggil RoleSeeder dulu (Supaya role 'admin' & 'member' terbentuk)
        $this->call(RoleSeeder::class);

        // 2. Buat Akun ADMIN
        $admin = User::factory()->create([
            'name' => 'Admin Ganteng',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        // Beri role 'admin' (Sesuai yg ada di RoleSeeder kamu)
        $admin->assignRole('admin');

        // 3. Buat Akun MEMBER
        $member = User::factory()->create([
            'name' => 'Member Setia',
            'email' => 'member@member.com',
            'password' => bcrypt('password'),
        ]);
        // Beri role 'member' (Sesuai yg ada di RoleSeeder kamu)
        $member->assignRole('member');
    }
}
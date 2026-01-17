<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);


        $admin = User::factory()->create([
            'name' => 'Admin Ganteng',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $member = User::factory()->create([
            'name' => 'Member Setia',
            'email' => 'member@member.com',
            'password' => bcrypt('password'),
        ]);
    
        $member->assignRole('member');
    }
}
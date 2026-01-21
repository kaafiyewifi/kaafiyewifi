<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@kaafiye.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password123')]
        );

        $user->assignRole('super_admin');
    }
}

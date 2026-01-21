<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        foreach (['super_admin', 'admin', 'agent'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create super admin user
        $user = User::firstOrCreate(
            ['email' => 'kaafiyewifi@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('123456'),
            ]
        );

        if (!$user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}

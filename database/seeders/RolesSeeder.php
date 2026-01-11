<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage users',
            'manage customers',
            'manage subscriptions',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $manager    = Role::firstOrCreate(['name' => 'Manager']);
        $support    = Role::firstOrCreate(['name' => 'Support']);

        $superAdmin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'view dashboard',
            'manage customers',
            'manage subscriptions',
        ]);

        $support->syncPermissions([
            'view dashboard',
        ]);
    }
}

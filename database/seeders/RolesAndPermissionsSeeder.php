<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Permissions (ku dar manage routers)
        $permissions = [
            'view dashboard',
            'manage users',
            'manage locations',
            'manage routers',   // ✅ NEW
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ✅ Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $operator   = Role::firstOrCreate(['name' => 'operator']);

        // ✅ Assign permissions
        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'view dashboard',
            'manage users',
            'manage locations',
            'manage routers',  // ✅ NEW
        ]);

        $operator->syncPermissions([
            'view dashboard',
        ]);
    }
}

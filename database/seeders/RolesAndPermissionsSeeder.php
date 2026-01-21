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
        // Clear cached roles/permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Permissions (kuwa aad sheegtay + kuwa aasaaska ah)
        $permissions = [
            'view dashboard',
            'manage users',
            'manage locations',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ✅ Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $operator   = Role::firstOrCreate(['name' => 'operator']);

        // ✅ Assign permissions
        // Super Admin => all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin => manage users + view dashboard (+ manage locations optional)
        $admin->syncPermissions([
            'view dashboard',
            'manage users',
            'manage locations', // haddii aadan rabin admin inuu locations maamulo, ka saar line-kan
        ]);

        // Operator => view dashboard only (ama waxaad ku dari kartaa wax kale)
        $operator->syncPermissions([
            'view dashboard',
        ]);
    }
}

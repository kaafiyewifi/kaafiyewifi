<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LocationAccessCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_sees_only_customers_in_assigned_locations(): void
    {
        // ✅ Make sure Spatie permission cache doesn't block new roles/permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ✅ Create role + permission for 'web' guard
        $roleAgent = Role::findOrCreate('agent', 'web');
        $permCustomers = Permission::findOrCreate('manage customers', 'web');

        // Optional: ensure role has permission (not required if user has it directly)
        $roleAgent->givePermissionTo($permCustomers);

        $locA = Location::factory()->create(['name' => 'Loc A']);
        $locB = Location::factory()->create(['name' => 'Loc B']);

        $op = User::factory()->create(['status' => 'active']);

        // ✅ Assign role + permission
        $op->assignRole($roleAgent);
        $op->givePermissionTo($permCustomers);

        // ✅ Assign operator to Location A only
        $op->locations()->sync([$locA->id]);

        Customer::factory()->create(['location_id' => $locA->id, 'username' => 'a1']);
        Customer::factory()->create(['location_id' => $locB->id, 'username' => 'b1']);

        // ✅ Correct route (admin prefix)
        $res = $this->actingAs($op)->get('/admin/customers');

        $res->assertOk();
        $res->assertSee('a1');
        $res->assertDontSee('b1');
    }
}

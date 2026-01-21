<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'kaafiyewifi@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123456'),
                'status' => 'active',
            ]
        );

        // ✅ role assign
        $user->syncRoles(['super_admin']);

        // ✅ attach all locations (haddii locations jiraan)
        $locationIds = Location::pluck('id')->toArray();
        $user->locations()->sync($locationIds);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            'Yaqshid',
            'Hodan',
            'Wadajir',
            'Karaan',
            'Ceelasha',
            'Dayniile',
            'Kaaraan',
            'Shibis',
            'Abdiaziz',
            'Dharkenley',
        ];

        foreach ($locations as $name) {
            Location::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}

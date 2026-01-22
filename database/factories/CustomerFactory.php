<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'full_name'   => $this->faker->name(),
            'phone'       => '61' . $this->faker->unique()->numberBetween(1000000, 9999999),
            'is_active'   => true,
            'location_id' => Location::factory(),

            // username + password waxaa si auto ah u sameynaya Model booted()
            'username'    => null,
            'password'    => null,
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Branch ' . fake()->unique()->city(),
            'status' => 'active',   // ama 1, ku xiran schema-gaaga
            'address' => fake()->address(),
            'city' => fake()->city(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            // ✅ Email unique
            'email' => fake()->unique()->safeEmail(),

            // ✅ Phone unique (Somali-style example: 61xxxxxxx)
            // Haddii phone-kaaga format kale yahay, i sheeg waan kuu beddeli.
            'phone' => fake()->unique()->numerify('61#######'),

            // ✅ Active by default (so login works in tests)
            'status' => 'active',

            'email_verified_at' => now(),

            // ✅ Default password = "password"
            'password' => static::$password ??= Hash::make('password'),

            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * (Optional) Make user inactive (useful for testing).
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }
}

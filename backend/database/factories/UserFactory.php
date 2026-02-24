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
    // protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'nombre_usuario' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'contrasenya' => Hash::make('1234'),
            'telefono' => $this->faker->unique()->phoneNumber(),
            'direccion' => $this->faker->address(),
            'latitud' => $this->faker->randomFloat(8, -90, 90),
            'longitud' => $this->faker->randomFloat(8, -180, 180),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    // public function unverified(): static {
    // return $this->state(fn(array $attributes) => [
    // 'email_verified_at' => null,
    // ]);
    // }
}

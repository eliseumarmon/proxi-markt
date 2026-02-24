<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PuntoEntrega>
 */
class PuntoEntregaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'nombre_punto' => $this->faker->word(),
            'direccion_punto' => $this->faker->address(),
            'latitud' => $this->faker->randomFloat(8, -90, 90),
            'longitud' => $this->faker->randomFloat(8, -180, 180),
        ];
    }
}

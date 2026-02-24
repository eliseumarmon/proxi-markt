<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $alimentos = [
            'Manzana',
            'Pera',
            'Plátano',
            'Sandía',
            'Uva',
            'Kiwi',
            'Mango',
            'Fresa',
            'Piña',
            'Melocotón',
            'Tomate',
            'Lechuga',
            'Zanahoria',
            'Brócoli',
            'Espinaca',
            'Calabacín',
            'Berenjena',
            'Pimiento',
            'Cebolla',
            'Ajo',
            'Pepino',
            'Acelga',
            'Coliflor',
            'Patata',
            'Calabaza'
        ];
        $numeroFoto = $this->faker->numberBetween(1, 8);

        return [
            'id_categoria' => $this->faker->numberBetween(1, 5),
            'nombre_producto' => $this->faker->randomElement($alimentos),
            'precio' => $this->faker->randomFloat(2, 0, 999.99),
            'descripcion' => $this->faker->paragraph(),
            'stock_total' => $this->faker->numberBetween(1, 200),
            'imagen' => "productos/foto" . $numeroFoto . ".jpg",
        ];
    }
}

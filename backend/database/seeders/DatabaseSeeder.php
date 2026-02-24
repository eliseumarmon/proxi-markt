<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Producto;
use App\Models\PuntoEntrega;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {

        $this->call(CategoriaSeeder::class);

        User::factory(10)->create();

        $usuarios = User::all();

        foreach ($usuarios as $usuario) {

            $puntos = PuntoEntrega::factory(rand(1,3))->create([
                'id_usuario' => $usuario->id,
            ]);

            // $puntos = PuntoEntrega::where('id_usuario','=', $usuario->id)->get();

            foreach ($puntos as $punto) {
                Producto::factory(rand(1,5))->create([
                    'id_usuario' => $usuario->id,
                    'id_puntoentrega' => $punto->id
                ]);
            }
        }
    }
}

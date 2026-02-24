<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        Categoria::create(["nombre_categoria" => "Frutas"]);
        Categoria::create(["nombre_categoria" => "Verduras"]);
        Categoria::create(["nombre_categoria" => "Derivados animales"]);
        Categoria::create(["nombre_categoria" => "Artesanales"]);
        Categoria::create(["nombre_categoria" => "Otros"]);
    }
}

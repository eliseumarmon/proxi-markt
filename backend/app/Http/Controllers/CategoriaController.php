<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    // Función para guardar una nueva categoría en la base de datos
    public function store(Request $request) {
        try {
            // Intentamos crear un nuevo registro en la base de datos usando el modelo Categoria
            Categoria::create([
                // Guardamos el nombre de la categoría
                "nombre_categoria" => $request->nombre_categoria,
            ]);

            /* Si se crea correctamente, devolvemos un mensaje de éxito en formato JSON
            (El código 201 es para indicar el estado: Creado) */
            return response()->json(["message" => "Categoria creada"], 201);
            
        } catch (\Exception $err) {
            // Si ocurre algún error en el paso anterior ponemos el código 500 para indicar: Error interno del servidor
            return response()->json(["error" => $err], 500);
        }
    }

    // Función para listar todas las categorías
    public function index() 
    {
        // Buscamos todas las categorías en la base de datos con "all()" y las devolvemos directamente en formato JSON
        return response()->json(\App\Models\Categoria::all());
    }
}
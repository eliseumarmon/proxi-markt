<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Función para obtener los datos y la puntuación del usuario autenticado
    public function unUsuario(Request $request) {
        // 1. Obtenemos el usuario que está haciendo la petición actualmente
        $user = $request->user();
        
        /* 2. Calculamos la media de sus valoraciones.
        Laravel busca la relación 'valoracionesRecibidas', calcula la media de la columna 'valoracion'
        y añade ese resultado al objeto del usuario bajo el nombre 'puntuacion'. */
        $user->loadAvg('valoracionesRecibidas as puntuacion', 'valoracion');
        
        // 3. Devolvemos los datos del usuario con su puntuación media incluida en formato JSON
        return response()->json($user);
    }

    // Función para actualizar los datos de ubicación de un usuario concreto
    public function updateLocation(Request $request, User $usuario) {
        /* 1. Actualizamos directamente el registro del usuario en la base de datos
        pasándole un array con los nuevos valores recibidos en la petición ($request) */
        $usuario->update([
            'direccion' => $request->direccion, // Texto con la dirección
            'latitud' => $request->latitud, // Coordenada de latitud
            'longitud' => $request->longitud, // Coordenada de longitud
        ]);

        /* 2. Devolvemos una respuesta JSON confirmando la actualización
        Incluimos un mensaje y el objeto del usuario ya actualizado.
        (El código 201 es para indicar el estado: Creado/Procesado) */
        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $usuario
        ], 201);
    }
}
<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Esto es una función para registrar un nuevo usuario
    public function createUser(UserRequest $request)
    {
        // Creamos un nuevo registro en la base de datos con los datos recibidos
        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario, // Nombre
            'email' => $request->email, // Email
            'contrasenya' => Hash::make($request->contrasenya), // Encriptamos la contraseña por seguridad
            'telefono' => $request->telefono, // Teléfono
        ]);

        /* Devolvemos una respuesta en formato JSON confirmando que todo ha ido bien
        (El código 201 es para indicar el estado: Creado) */
        return response()->json([
            'status' => 'true',
            'message' => 'Usuario creado correctamente',
        ], 201);
    }

    // Función para inicio de sesión
    public function loginUser(LoginUserRequest $request)
    {
        // Buscamos en la base de datos al primer usuario que tenga el email introducido
        $user = User::where('email', $request->email)->first();

        /* Si el usuario no existe o la contraseña introducida no coincide con la guardada
        devolvemos un mensaje de error (El código 401 es para indicar el estado: No autorizado) */
        if (!$user || !Hash::check($request->contrasenya, $user->contrasenya)) {
            return response()->json([
                'status' => 'false',
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Si los datos son correctos, generamos un token para este usuario
        $token = $user->createToken('api-token')->plainTextToken;

        /* Devolvemos el token y los datos del usuario para que pueda entrar
        (El código 200 es para indicar el estado: OK) */
        return response()->json([
            'status' => 'true',
            'token' => $token, 
            'user' => $user
        ], 200); 
    }

    // Función para cerrar sesión
    public function logout(Request $request)
    {
        // Identificamos al usuario actual y eliminamos su token para que ya no pueda hacer nada.
        $request->user()->currentAccessToken()->delete();

        /* Confirmamos que la sesión se ha cerrado correctamente
        (El código 200 es para indicar el estado: OK) */
        return response()->json(['message' => 'Sesión cerrada'], 200);
    }
}
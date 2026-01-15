<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function createUser(UserRequest $request)
    {
        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario,
            'email' => $request->email,
            'contrasenya' => Hash::make($request->contrasenya),
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'status' => 'true',
            'message' => 'Usuario creado correctamente',
        ], 201);
    }

    public function loginUser(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->contrasenya, $user->contrasenya)) {
            return response()->json([
                'status' => 'false',
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;


        return response()->json([
            'status' => 'true',
            'token' => $token, 
            'user' => $user
        ], 200); 
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $cookie = cookie()->forget('access_token');

        return response()->json(['message' => 'Sesión cerrada'], 200)->withCookie($cookie);
    }
}
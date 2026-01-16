<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function unUsuario(Request $request){
        return $request->user();
    }

    public function updateLocation(Request $request)
    {

        $user = User::find($request->user()->id);
        
        $user->update([
            'direccion' => $request->direccion,
            'latitud'   => $request->latitud,
            'longitud'  => $request->longitud,
        ]);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user
        ], 201);
    }
}

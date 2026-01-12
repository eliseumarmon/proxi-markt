<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::select('SELECT * FROM users');

        return response()->json($users);
    }

    public function store(Request $request){
        
        if( !$request->nombre_usuario || !$request->email || !$request->contrasenya || !$request->telefono){
            return response()->json([ 'message'=>'Faltan campos' ], 400 );
        }
        
        // $HashedPassword = Hash::make($request->contrasenya);

        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario,
            'email' => $request->email,
            'contrasenya' => Hash::make($request->contrasenya),
            'telefono' => $request->telefono
        ]);

        $user->save();
        
        // DB::insert('INSERT INTO usuarios (nombre_usuario, email, contrasenya, telefono) VALUES (?, ?, ?, ?)', [
        //     $request->nombre_usuario,
        //     $request->email,
        //     $HashedPassword,
        //     $request->telefono
        // ]);

        return response()->json([ 'message'=>'Creado' ], 201 );
    }

    public function login(Request $request){

        if( !$request->email || !$request->contrasenya ){
            return response()->json([ 'message'=>'Faltan campos' ], 400 );
        }

        $user = User::where('email', $request->email)->first();

        if($user){
            $passwordMatched = Hash::check($request->contrasenya, $user->contrasenya);
            if($passwordMatched){
                $token = $user->createToken('login_token')->plainTextToken;

                return response()->json([ 'token' => $token, 'message' => 'Login correcto' ], 200);

            }else{
                return response()->json([ 'message'=>'Contraseña incorrecta'], 401 );
            }
        }else{
            return response()->json([ 'message'=>'El usuario no existe' ], 401 );
        }
    }
}

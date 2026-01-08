<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends UserController
{
    public function index(){
        $users = User::all();
        return view('users.index.user', compact($users));
    }

    public function store(Request $request){
        User::create([
            'nombre_usuario'=>$request->nombre_usuario,
            'email'=>$request->email,
            'contrasenya'=>$request->contrasenya,
            'telefono'=>$request->telefono,
            
        ]);

        return respose()->json(['message'=>'Creado'],201);
    }
}

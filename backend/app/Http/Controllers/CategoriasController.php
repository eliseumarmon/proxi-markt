<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    public function index() 
    {
        return response()->json(\App\Models\Categoria::all());
    }
}

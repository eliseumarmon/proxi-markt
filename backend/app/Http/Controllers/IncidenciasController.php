<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidencias;

class IncidenciasController extends Controller
{
    public function index(Request $request)
    {
        $query = Incidencias::with('usuario');

        $incidencias = $query->paginate(6);

        return response()->json($incidencias);
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'mensaje' => 'required|string',
        ]);

        $user = $request->user();
        $validado['id_usuario'] = $user->id;

        Incidencias::create($validado);

        return response()->json([
            'message' => 'incidencia creada con exito',
        ], 201);
    }

    public function update(Request $request, $id_incidencia)
    {
        $incidencia = Incidencias::findOrFail($id_incidencia);

        $incidencia->estado = $request->input('estado');

        $incidencia->save();

        return response()->json([
            'message' => 'incidencia actualizada',
        ], 200);
    }
}

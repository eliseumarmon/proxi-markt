<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PuntoEntrega;

class PuntoEntregaController extends Controller
{
    /**
     * Listar todos los puntos de entrega (Para que el comprador elija uno)
     */
    public function index()
    {
        $puntos = PuntoEntrega::with('usuario')->get();
        return response()->json($puntos);
    }

    /**
     * Listar puntos de entrega de un vendedor específico
     */
    public function puntosPorVendedor($userId)
    {
        $puntos = PuntoEntrega::where('id_usuario', $userId)->get();
        return response()->json($puntos);
    }

    /**
     * Crear un nuevo punto de entrega (Para el agricultor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario'      => 'required|exists:usuarios,id',
            'nombre_punto'    => 'required|string|max:255',
            'direccion_punto' => 'nullable|string|max:255',
            'longitud'        => 'required|numeric',
            'latitud'         => 'required|numeric',
        ]);

        $punto = PuntoEntrega::create($request->all());

        return response()->json([
            'message' => 'Punto de entrega creado correctamente',
            'data'    => $punto
        ], 201);
    }

    /**
     * Eliminar un punto de entrega
     */
    public function destroy($id)
    {
        $punto = PuntoEntrega::findOrFail($id);
        $punto->delete();

        return response()->json(['message' => 'Punto de entrega eliminado']);
    }
}

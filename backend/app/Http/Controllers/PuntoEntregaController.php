<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PuntoEntrega;
use App\Http\Requests\PuntosEntregaRequest;

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
    public function puntosPorVendedor(Request $request)
    {
        $user = $request->user();
        $puntos = PuntoEntrega::where('id_usuario', $user->id)->get();
        return response()->json($puntos);
    }

    /**
     * Crear un nuevo punto de entrega (Para el agricultor)
     */
    public function store(PuntosEntregaRequest $request) 
    {
        // 1. Los datos ya vienen validados gracias al Request
        // 2. Extraemos el usuario del token de Sanctum
        $user = $request->user();

        // 3. Creamos el punto asociándolo al ID del usuario autenticado
        $punto = PuntoEntrega::create([
            'id_usuario'      => $user->id, 
            'nombre_punto'    => $request->nombre_punto,
            'direccion_punto' => $request->direccion_punto,
            'longitud'        => $request->longitud,
            'latitud'         => $request->latitud,
        ]);

        return response()->json([
            'status'  => true,
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

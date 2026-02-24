<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PuntoEntrega;
use App\Models\User;
use App\Http\Requests\PuntosEntregaRequest;

class PuntoEntregaController extends Controller
{

    public function index() {
        $puntos = PuntoEntrega::with('usuario')->get();
        return response()->json($puntos);
    }

    /**
     * Listar puntos de entrega de un vendedor específico
     */
    public function puntosPorVendedor(Request $request, User $usuario) {
        $puntos = PuntoEntrega::where('id_usuario', $usuario->id)->get();
        return response()->json($puntos);
    }

    /**
     * Crear un nuevo punto de entrega (Para el agricultor)
     */
    public function store(PuntosEntregaRequest $request) {
        // 1. Los datos ya vienen validados gracias al Request
        // 2. Extraemos el usuario del token de Sanctum
        $user = $request->user();

        // 3. Creamos el punto asociándolo al ID del usuario autenticado
        $punto = PuntoEntrega::create([
            'id_usuario' => $user->id,
            'nombre_punto' => $request->nombre_punto,
            'direccion_punto' => $request->direccion_punto,
            'longitud' => $request->longitud,
            'latitud' => $request->latitud,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Punto de entrega creado correctamente',
            'data' => $punto
        ], 201);
    }

    /**
     * Eliminar un punto de entrega
     */
    public function destroy(Request $request, $id) {
        $user = $request->user();
        // Buscamos el punto que coincida con el ID Y que pertenezca al usuario autenticado
        $punto = PuntoEntrega::where('id', $id)->where('id_usuario', $user->id)->first();

        if (!$punto) {
            return response()->json(['message' => 'No se encontró el punto o no tienes permiso'], 404);
        }

        $punto->delete();

        return response()->json(['message' => 'Punto de entrega eliminado']);
    }

    public function puntosRadio(Request $request, $radio) {
        $latUsuario = $request->query('lat');
        $lngUsuario = $request->query('lng');
        $usuario = $request->user();
        $radioTierra = 6371;

        if (!$latUsuario || !$lngUsuario) {
            return response()->json(['error' => 'Faltan coordenadas'], 400);
        }

        $puntos = PuntoEntrega::select('*')
            ->selectRaw(
                "( ? * acos( cos( radians(?) ) * cos( radians( latitud ) ) * cos( radians( longitud ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitud ) ) ) ) AS distancia",
                [$radioTierra, $latUsuario, $lngUsuario, $latUsuario]
            )
            ->with([
                'usuario:id,nombre_usuario',
                'productos'
            ])
            ->having('distancia', '<=', $radio)
            ->orderBy('distancia', 'asc')
            ->where('id_usuario', '!=', $usuario->id)
            ->get();

        return response()->json($puntos);
    }
}

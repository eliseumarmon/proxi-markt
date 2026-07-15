<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favoritos;

class FavoritosController extends Controller
{

    public function favoritosporuser(Request $request)
    {
        $user = $request->user();

        $favoritos = Favoritos::with(['producto.punto_entrega'])
            ->where('id_usuario', $user->id)
            ->paginate(4);

        return response()->json($favoritos);
    }

    public function store(Request $request, $id_producto)
    {
        $user = $request->user();

        $request->merge(['id_producto' => $id_producto]);
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
        ]);

        Favoritos::firstOrCreate([
            'id_usuario' => $user->id,
            'id_producto' => $id_producto,
        ]);

        return response()->json(['message' => 'Favorito guardado'], 201);
        
    }

    public function destroy(Request $request, $id_producto)
    {
        $user = $request->user();

        $favorito = Favoritos::where('id_usuario', $user->id)
            ->where('id_producto', $id_producto);

        $favorito->delete();
        return response()->noContent();

    }

    // esta funcio torna booleano per a comprobar que existix
    public function sacarfavorito(Request $request, $id_producto)
    {
        $user = $request->user();

        $esFavorito = Favoritos::where('id_usuario', $user->id)
            ->where('id_producto', $id_producto)
            ->exists();

        return response()->json(['es_favorito' => $esFavorito]);
    }


}

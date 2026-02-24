<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{

    public function index(Request $request) {
    $query = Producto::with(['categoria', 'usuario', 'punto_entrega']);

    if ($request->has('search') && $request->search != '') {
        $query->where('nombre_producto', 'like', '%' . $request->search . '%');
    }

    if ($request->has('categorias') && $request->categorias != '') {
        $categorias = is_array($request->categorias) 
                      ? $request->categorias 
                      : explode(',', $request->categorias);
                      
        $query->whereHas('categoria', function($q) use ($categorias) {
            $q->whereIn('nombre_categoria', $categorias);
        });
    }
    
    $productos = $query->paginate(9);

    return response()->json($productos);
}

    public function productosPorUsuario(Request $request) {
        $user = $request->user();

        $productos = Producto::with('categoria')
            ->where('id_usuario', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(7);
    
        return response()->json($productos);
    }

    /**
     * Crear un nuevo producto (Para el agricultor)
     */
    public function store(Request $request) {

        $validado = $request->validate([
            'id_categoria' => 'required|exists:categorias,id',
            'id_puntoentrega' => 'required|exists:puntos_entrega,id',
            'nombre_producto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock_total' => 'required|integer|min:1',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $user = $request->user();

        $validado['id_usuario'] = $user->id;
        $validado['estado'] = $validado['estado'] ?? 'disponible';

        if ($request->hasFile('imagen')) {
            $validado['imagen'] = $request->file('imagen')->store('productos', 'public');
        } else {
            $validado['imagen'] = 'productos/default.png';
        }

        Producto::create($validado);

        return response()->json([
            'message' => 'Producto publicado con éxito',
            'producto' => $validado
        ], 201);
    } // Aquí termina la función store correctamente

    /**
     * Ver detalle de un producto específico
     */
    public function show($id) {
        $producto = Producto::with('categoria', 'usuario', 'punto_entrega')->findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Actualizar datos del producto
     */
    public function update(Request $request, $id) {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'nombre_producto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'numeric|min:0',
            'stock_total' => 'integer|min:0',
            'id_puntoentrega' => 'required|exists:puntos_entrega,id',
            'imagen' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return response()->json([
            'message' => 'Producto actualizado con éxito',
            'producto' => $producto
        ], 200);
    }

    public function destroy($id) {

        $producto = Producto::findOrFail($id);

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente'], 200);

    }

    public function obtenerProductosPunto(PuntoEntrega $punto) {
        $productos = Producto::with(['categoria', 'punto_entrega'])
            ->where('id_puntoentrega', $punto->id)
            ->get();

        if ($productos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No hay productos para este punto de entrega.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'productos' => $productos
        ]);
    }
}
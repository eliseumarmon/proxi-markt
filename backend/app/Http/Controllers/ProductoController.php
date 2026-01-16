<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Mostrar todos los productos disponibles (Para la tienda/mapa)
     */
    public function index()
    {
        // Nota: En tu SQL no existe stock_real, así que filtramos solo por stock_total
        $productos = Producto::with('categoria')
            ->where('estado', 'disponible')
            ->where('stock_total', '>', 0)
            ->get();

        return response()->json($productos);
    }

    /**
     * Crear un nuevo producto (Para el agricultor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_categoria'    => 'required|exists:categorias,id',
            'id_puntoentrega' => 'required|exists:puntos_entrega,id', 
            'nombre_producto' => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'precio'          => 'required|numeric|min:0',
            'stock_total'     => 'required|integer|min:1',
            'imagen'          => 'nullable|image|max:2048',
        ]);

        $user = $request->user();
        $rutaImagen = null;

        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create([
            'id_categoria'    => $request->id_categoria,
            'id_usuario'      => $user->id, 
            'id_puntoentrega' => $request->id_puntoentrega, 
            'nombre_producto' => $request->nombre_producto,
            'descripcion'     => $request->descripcion,
            'precio'          => $request->precio,
            'stock_total'     => $request->stock_total,
            'imagen'          => $rutaImagen, 
            'estado'          => 'disponible',
        ]);

        return response()->json([
            'message' => 'Producto publicado con éxito',
            'producto' => $producto
        ], 201);
    } // Aquí termina la función store correctamente

    /**
     * Ver detalle de un producto específico
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Actualizar datos del producto
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'precio'      => 'numeric|min:0',
            'stock_total' => 'integer|min:0',
        ]);

        $producto->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado',
            'producto' => $producto
        ]);
    }
}
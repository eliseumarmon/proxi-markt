<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    /**
     * Mostrar todos los productos disponibles (Para la tienda/mapa)
     */
    public function index()
    {
        // Traemos solo los que tienen stock real y están marcados como disponibles
        $productos = Producto::with('categoria')
            ->where('estado', 'disponible')
            ->where('stock_real', '>', 0)
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
            'nombre_producto' => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'precio'          => 'required|numeric|min:0',
            'stock_total'     => 'required|integer|min:1',
            'imagen'          => 'nullable|string', // Aquí podrías manejar subida de archivos luego
        ]);

        // Al crear el producto, stock_reserva empieza en 0.
        // El stock_real se calculará solo en el modelo gracias al método booted()
        $producto = Producto::create([
            'id_categoria'    => $request->id_categoria,
            'nombre_producto' => $request->nombre_producto,
            'descripcion'     => $request->descripcion,
            'precio'          => $request->precio,
            'stock_total'     => $request->stock_total,
            'stock_reserva'   => 0,
            'imagen'          => $request->imagen,
            'estado'          => 'disponible',
        ]);

        return response()->json([
            'message' => 'Producto publicado con éxito',
            'producto' => $producto
        ], 201);
    }

    /**
     * Ver detalle de un producto específico
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Actualizar datos del producto (Ej: el agricultor quiere cambiar el precio o añadir stock)
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
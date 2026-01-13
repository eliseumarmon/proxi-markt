<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
  
    public function index()
    {
        $productos = Producto::with('categoria')
            ->where('estado', 'disponible')
            ->where('stock', '>', 0)
            ->get();

        return response()->json($productos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_categoria'    => 'required|exists:categorias,id',
            'nombre_producto' => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'precio'          => 'required|numeric|min:0',
            'stock'     => 'required|integer|min:1',
            'imagen'          => 'nullable|string',
        ]);

        $producto = Producto::create([
            'id_categoria'    => $request->id_categoria,
            'nombre_producto' => $request->nombre_producto,
            'descripcion'     => $request->descripcion,
            'precio'          => $request->precio,
            'stock'     => $request->stock,
            'imagen'          => $request->imagen,
            'estado'          => 'disponible',
        ]);

        return response()->json([
            'message' => 'Producto publicado con éxito',
            'producto' => $producto
        ], 201);
    }

    
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);
        return response()->json($producto);
    }

   
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'precio'      => 'numeric|min:0',
            'stock' => 'integer|min:0',
        ]);

        $producto->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado',
            'producto' => $producto
        ]);
    }
}
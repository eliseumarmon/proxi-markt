<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompraVenta;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class CompraVentaController extends Controller
{

    public function store(Request $request)
    {
        
        $request->validate([
            'id_producto'   => 'required|exists:productos,id',
            'id_comprador'  => 'required|exists:usuarios,id',
            'id_vendedor'   => 'required|exists:usuarios,id',
            'id_punto'      => 'required|exists:puntos_entrega,id',
            'cantidad_total'=> 'required|integer|min:1',
        ]);

 
        $producto = Producto::findOrFail($request->id_producto);
        $cantidad = $request->cantidad_total;

        if ($producto->stock_real < $cantidad) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible.',
                'stock_disponible' => $producto->stock_real
            ], 400);
        }

        try {
            DB::beginTransaction();

            $compraventa = CompraVenta::create([
                'id_producto'   => $request->id_producto,
                'id_comprador'  => $request->id_comprador,
                'id_vendedor'   => $request->id_vendedor,
                'id_punto'      => $request->id_punto,
                'cantidad_total'=> $cantidad,
                'estado'        => 'pendiente', // Estado inicial
            ]);


            $producto->increment('stock_reserva', $cantidad);

            DB::commit();

            return response()->json([
                'message' => 'Reserva creada correctamente',
                'data'    => $compraventa
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la reserva'], 500);
        }
    }

    public function completarVenta($id)
    {
        $venta = CompraVenta::findOrFail($id);

        if ($venta->estado !== 'pendiente' && $venta->estado !== 'en curso') {
            return response()->json(['message' => 'La venta no se puede completar'], 400);
        }

    }
}
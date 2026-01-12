<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompraVenta;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class CompraVentaController extends Controller
{
    /**
     * Registrar una nueva compraventa (Reserva)
     */
    public function store(Request $request)
    {
        // 1. Validación de datos de entrada
        $request->validate([
            'id_producto'   => 'required|exists:productos,id',
            'id_comprador'  => 'required|exists:usuarios,id',
            'id_vendedor'   => 'required|exists:usuarios,id',
            'id_punto'      => 'required|exists:puntos_entrega,id',
            'cantidad_total'=> 'required|integer|min:1',
        ]);

        // 2. Buscar el producto
        $producto = Producto::findOrFail($request->id_producto);
        $cantidad = $request->cantidad_total;

        // 3. Verificar si hay stock real suficiente
        if ($producto->stock_real < $cantidad) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible.',
                'stock_disponible' => $producto->stock_real
            ], 400);
        }

        // 4. Usar una Transacción de Base de Datos para asegurar que no haya errores
        try {
            DB::beginTransaction();

            // A. Crear la Compraventa
            $compraventa = CompraVenta::create([
                'id_producto'   => $request->id_producto,
                'id_comprador'  => $request->id_comprador,
                'id_vendedor'   => $request->id_vendedor,
                'id_punto'      => $request->id_punto,
                'cantidad_total'=> $cantidad,
                'estado'        => 'pendiente', // Estado inicial
            ]);

            // B. Actualizar Stock: Se mueve de 'real' a 'reserva'
            // El stock_total NO cambia aún, solo se "aparta" el producto
            $producto->increment('stock_reserva', $cantidad);
            // Nota: stock_real se recalcula automáticamente por el método booted() del modelo Producto

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

    /**
     * Finalizar una venta (Cuando el comprador recoge el producto)
     */
    public function completarVenta($id)
    {
        $venta = CompraVenta::findOrFail($id);

        if ($venta->estado !== 'pendiente' && $venta->estado !== 'en curso') {
            return response()->json(['message' => 'La venta no se puede completar'], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Cambiar estado
            $venta->update(['estado' => 'completado']);

            // 2. Descontar del Stock Total definitivamente
            $producto = $venta->producto;
            $producto->decrement('stock_total', $venta->cantidad_total);
            $producto->decrement('stock_reserva', $venta->cantidad_total);
            
            DB::commit();
            return response()->json(['message' => 'Venta finalizada y stock descontado']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al completar la venta'], 500);
        }
    }
}
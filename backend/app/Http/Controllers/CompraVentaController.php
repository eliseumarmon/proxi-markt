<?php

// Importamos los modelos y herramientas de Laravel que vamos a necesitar
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompraVenta;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CompraVentaController extends Controller
{
    // Función para crear una nueva reserva de compra
    public function store(Request $request, Producto $producto) {
        $datosValidados = $request->validate([
            'fecha_prevista' => 'required|date',
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            $resultado = DB::transaction(function () use ($producto, $datosValidados) {
                $productoBloqueado = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
                $compradorId = Auth::id();

                if ((int) $productoBloqueado->id_usuario === (int) $compradorId) {
                    return response()->json([
                        'message' => 'No puedes comprar tu propio producto.'
                    ], 422);
                }

                $puntoPerteneceAlVendedor = PuntoEntrega::where('id', $productoBloqueado->id_puntoentrega)
                    ->where('id_usuario', $productoBloqueado->id_usuario)
                    ->exists();

                if (!$puntoPerteneceAlVendedor) {
                    return response()->json([
                        'message' => 'El punto de entrega no pertenece al vendedor del producto.'
                    ], 422);
                }

                if ((int) $datosValidados['cantidad'] > (int) $productoBloqueado->stock_real) {
                    return response()->json([
                        'message' => 'No hay stock suficiente para reservar esa cantidad.'
                    ], 422);
                }

                $compraVenta = CompraVenta::create([
                    'id_comprador' => $compradorId,
                    'id_vendedor' => $productoBloqueado->id_usuario,
                    'id_producto' => $productoBloqueado->id,
                    'id_punto' => $productoBloqueado->id_puntoentrega,
                    'cantidad' => $datosValidados['cantidad'],
                    'precio' => $productoBloqueado->precio,
                    'fecha_prevista' => $datosValidados['fecha_prevista'],
                ]);

                $productoBloqueado->increment('stock_reserva', $datosValidados['cantidad']);

                return $compraVenta;
            });

            if ($resultado instanceof JsonResponse) {
                return $resultado;
            }

            return response()->json([
                'message' => 'Reserva creada correctamente',
                'data' => $resultado
            ], 201);

        } catch (Throwable $err) {
            return response()->json(['message' => $err->getMessage()], 500);
        }
    }

    // Función para ver las compras que he hecho yo (como comprador)
    public function misCompras() {
        // Buscamos compras donde el comprador sea el usuario actual
        $compras = CompraVenta::where('id_comprador', Auth::id())
            ->with('producto', 'vendedor') // Traemos también la info del producto y del vendedor
            ->orderBy('created_at', 'desc') // Ordenamos de más reciente a más antiguo
            ->paginate(3); // Lo dividimos en páginas de 3 en 3 elementos

        // Devolvemos la lista
        return response()->json($compras);
    }

    // Función para ver las ventas que he hecho yo (como vendedor)
    public function misVentas() {
        // Buscamos ventas donde el vendedor sea el usuario actual
        $ventas = CompraVenta::where('id_vendedor', Auth::id())
            ->with(['producto', 'comprador']) // Traemos la info del producto y del comprador
            ->orderBy('created_at', 'desc') // Ordenamos por fecha
            ->paginate(3); // Paginamos de 3 en 3

        // Devolvemos la lista
        return response()->json($ventas);
    }

    // Función para ver todas mis transacciones (tanto compras como ventas)
    public function misComandas() {
        $userId = Auth::id(); // Guardamos el ID del usuario

        // Buscamos donde el usuario sea comprador o vendedor
        $comandas = CompraVenta::where('id_comprador', $userId)->orWhere('id_vendedor', $userId)
            ->with(['producto', 'comprador', 'vendedor']) // Traemos toda la info relacionada
            // Además, comprobamos si el usuario actual ya ha valorado esta transacción
            ->withExists([
                'valoraciones as ya_valorado' => function ($query) use ($userId) {
                    $query->where('id_valorador', $userId);
                }
            ])
            ->orderBy('created_at', 'desc') // Ordenamos por fecha
            ->get(); // Obtenemos todos los resultados (sin paginar)

        // Devolvemos la cantidad total encontrada y los datos
        return response()->json([
            'cantidad_encontrada' => $comandas->count(),
            'datos' => $comandas
        ], 200);
    }

    // Función interna (no se llama por ruta directa) para ajustar el stock según el estado de la venta
    public function completarVenta(CompraVenta $compraventa) {
        // Buscamos el producto de esta compraventa
        $producto = Producto::whereKey($compraventa->id_producto)->lockForUpdate()->firstOrFail();

        // Miramos en qué estado se ha quedado la transacción
        switch ($compraventa->estado) {
            case 'completado':
                // Si se vendió, quitamos el producto del stock reservado y también del stock total
                $producto->decrement('stock_reserva', $compraventa->cantidad);
                $producto->decrement('stock_total', $compraventa->cantidad);
                break;
            case 'cancelado':
                // Si se canceló, solo quitamos la reserva (el producto vuelve a estar disponible para otros)
                $producto->decrement('stock_reserva', $compraventa->cantidad);
                break;
            case 'en curso':
            case 'pendiente':
            case 'valorado':
                // Si está en curso o pendiente, no hacemos nada con el stock por ahora
                break;
            default:
                // Si el estado es un diferente, lanzamos un error
                throw new Exception('Estado de transacción erróneo o no procesable.');
        }
    }

    // Función para cambiar el estado de una compraventa
    public function actualizarEstado(Request $request, CompraVenta $compraventa) {
        // Verificamos que el estado que nos envían sea válido
        $request->validate([
            'estado' => 'required|string|in:pendiente,en curso,cancelado,completado,valorado'
        ]);

        $userId = Auth::id();

        if ((int) $compraventa->id_comprador !== (int) $userId && (int) $compraventa->id_vendedor !== (int) $userId) {
            return response()->json(['message' => 'No autorizado para actualizar esta compraventa.'], 403);
        }

        $nuevoEstado = $request->estado;
        $estadoActual = $compraventa->estado;

        if ($nuevoEstado === $estadoActual) {
            return response()->json(['message' => 'La compraventa ya está en ese estado.'], 200);
        }

        if (!$this->transicionPermitida($compraventa, $nuevoEstado, $userId)) {
            return response()->json(['message' => 'Transición de estado no permitida.'], 422);
        }

        try {
            // Iniciamos otra vez la red de seguridad de la base de datos
            DB::beginTransaction();
            
            // Actualizamos el estado de la transacción con el nuevo estado
            $compraventa->update(['estado' => $nuevoEstado]);
            
            // Llamamos a la función de arriba para que ajuste el stock si es necesario
            $this->completarVenta($compraventa);
            
            // Si todo ha ido bien, guardamos los cambios
            DB::commit();
            
            // Y devolvemos mensaje de éxito para indicar que ha ido todo bien.
            return response()->json(['message' => 'Actualización de stock correcta.'], 201);
            
        } catch (Throwable $err) {
            // Si algo falla, deshacemos los cambios para evitar errores en el stock en la base de datos
            DB::rollback();
            // Y devolvemos el error
            return response()->json(['message' => $err->getMessage()]);
        }
    }

    private function transicionPermitida(CompraVenta $compraventa, string $nuevoEstado, int $userId): bool {
        $esVendedor = (int) $compraventa->id_vendedor === (int) $userId;
        $esComprador = (int) $compraventa->id_comprador === (int) $userId;

        return match ($compraventa->estado) {
            'pendiente' => ($esVendedor && in_array($nuevoEstado, ['en curso', 'cancelado'], true))
                || ($esComprador && $nuevoEstado === 'cancelado'),
            'en curso' => ($esVendedor && in_array($nuevoEstado, ['completado', 'cancelado'], true))
                || ($esComprador && $nuevoEstado === 'cancelado'),
            'completado' => $nuevoEstado === 'valorado',
            default => false,
        };
    }
}

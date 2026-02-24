<?php

// Importamos los modelos y herramientas de Laravel que vamos a necesitar
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\CompraVenta;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompraVentaController extends Controller
{
    // Función para crear una nueva reserva de compra
    public function store(Request $request, Producto $producto) {
        // 1. Verificamos que los datos que nos envían sean correctos y válidos
        $compraVentaValidada = $request->validate([
            'id_vendedor' => 'required|exists:usuarios,id|not_in:' . Auth::id(), // El vendedor debe existir, y NO puede ser el mismo usuario que está comprando
            'id_punto' => 'required|exists:puntos_entrega,id', // El punto de entrega debe existir en nuestra base de datos
            'fecha_prevista' => 'required|date', // La fecha debe ser una fecha válida
            'cantidad' => "required|integer|min:1|lte:{$producto->stock_real}", // La cantidad debe ser al menos 1, y no puede superar el stock real que tiene el producto
        ]);

        // Intentamos hacer todo el proceso de guardado
        try {
            /* Iniciamos una Transacción. Esto es una red de seguridad: si algo falla a mitad 
            del proceso, Laravel deshará todos los cambios para no dejar datos a medias. */
            DB::beginTransaction();

            // Añadimos a los datos validados la información que faltaba:
            $compraVentaValidada['id_comprador'] = Auth::id(); // El ID del usuario conectado
            $compraVentaValidada['id_producto'] = $producto->id; // El ID del producto
            $compraVentaValidada['precio'] = $producto->precio; // El precio actual del producto

            // Creamos el registro de la compra-venta en la base de datos
            CompraVenta::create($compraVentaValidada);

            // Aumentamos el "stock reservado" del producto para que nadie más lo pueda comprar
            $producto->increment('stock_reserva', $request->cantidad);

            // Si todo ha ido bien. Guardamos los cambios definitivamente en la base de datos.
            DB::commit();

            // Devolvemos un mensaje de éxito (El código 201 es para indicar el estado: Creado).
            return response()->json([
                'message' => 'Reserva creada correctamente',
                'data' => $compraVentaValidada
            ], 201);

        } catch (Exception $err) {
            // Si algo falló en el 'try', anulamos cualquier cambio que se haya intentado hacer en la BD
            DB::rollBack();
            // Devolvemos el mensaje de error (El código 500 es para indicar el estado: Error de servidor)
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
        $producto = Producto::find($compraventa->id_producto);

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

        try {
            // Iniciamos otra vez la red de seguridad de la base de datos
            DB::beginTransaction();
            
            // Actualizamos el estado de la transacción con el nuevo estado
            $compraventa->update(['estado' => $request->estado]);
            
            // Llamamos a la función de arriba para que ajuste el stock si es necesario
            $this->completarVenta($compraventa);
            
            // Si todo ha ido bien, guardamos los cambios
            DB::commit();
            
            // Y devolvemos mensaje de éxito para indicar que ha ido todo bien.
            return response()->json(['message' => 'Actualización de stock correcta.'], 201);
            
        } catch (Exception $err) {
            // Si algo falla, deshacemos los cambios para evitar errores en el stock en la base de datos
            DB::rollback();
            // Y devolvemos el error
            return response()->json(['message' => $err->getMessage()]);
        }
    }
}
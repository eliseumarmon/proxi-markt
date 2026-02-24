<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use App\Http\Requests\StoreValoracionRequest;
use App\Models\CompraVenta;
use App\Models\Valoracion;
use Illuminate\Support\Facades\Auth;

class ValoracionController extends Controller
{
    // Función para listar las valoraciones que ha recibido el usuario actual
    public function index() {
        // 1. Buscamos en la tabla de valoraciones aquellas donde el usuario actual sea el "valorado"
        $valoraciones = Valoracion::where("id_valorado", "=", Auth::id())
            // 2. Traemos de golpe la información del usuario que escribió la reseña ('emisor') 
            // y los detalles de la transacción y el producto ('compraventa.producto')
            ->with(['emisor', 'compraventa.producto'])
            // 3. Ordenamos los resultados para que las valoraciones más recientes salgan primero
            ->latest()
            // 4. Dividimos los resultados en páginas de 5 en 5 para no saturar la respuesta
            ->paginate(5);

        // 5. Devolvemos la lista paginada en formato JSON
        return response()->json($valoraciones);
    }

    // Función para guardar una nueva valoración sobre una compra/venta específica
    public function store(StoreValoracionRequest $request, CompraVenta $compraventa) {
        
        // 1. Obtenemos el ID del usuario que está escribiendo la reseña (el emisor)
        $idEmisor = Auth::id();

        /* 2. Identificamos automáticamente quién es el receptor de la valoración.
        Si el usuario actual (emisor) es el comprador, entonces el receptor debe ser el vendedor.
        Si no es el comprador, significa que es el vendedor, por lo que el receptor será el comprador.
        Esto asegura que la reseña siempre vaya a la otra parte implicada en la transacción. */
        $idReceptor = $idEmisor == $compraventa->id_comprador ? $compraventa->id_vendedor : $compraventa->id_comprador;

        // 3. Creamos el nuevo registro de la valoración en la base de datos
        Valoracion::create([
            'id_valorador' => $idEmisor, // Quién pone la nota
            'id_valorado' => $idReceptor, // Quién recibe la nota
            'id_venta' => $compraventa->id, // A qué transacción pertenece esta valoración
            'valoracion' => $request->valoracion, // La puntuación numérica
            'comentario' => $request->comentario // El texto de la reseña
        ]);

        /* Nota del código: Esta línea está comentada. Si se descomentara, serviría para 
        cambiar automáticamente el estado de la transacción a 'valorado' una vez guardada la reseña. */
        // $compraventa::update(['estado' => 'valorado']);
        
        // 4. Devolvemos la respuesta en formato JSON confirmando que todo ha ido bien.
        return response()->json(['status' => 'success']);
    }
}
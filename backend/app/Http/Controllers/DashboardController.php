<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\CompraVenta;

class DashboardController extends Controller
{
    // Función principal que reúne toda la información necesaria para el panel de control del usuario
    public function index(Request $request)
    {
        // 1. Identificamos al usuario: Obtenemos el ID de la persona que está conectada haciendo la petición
        $userId = $request->user()->id;

        // 2. Buscamos sus productos: Le pedimos a la base de datos todos los productos
        // cuya columna 'id_usuario' coincida con el ID de nuestro usuario actual.
        $productos = Producto::where('id_usuario', $userId)->get();

        // 3. Buscamos sus ventas: Buscamos en las transacciones donde este usuario sea el vendedor ('id_vendedor').
        // Usamos 'with' para traernos los datos del producto vendido y de la persona que lo compró.
        $ventas = Compraventa::with('producto', 'comprador')
            ->where('id_vendedor', $userId)
            ->get();

        // 4. Buscamos sus compras: Hacemos lo mismo pero al revés. Buscamos donde el usuario sea el comprador ('id_comprador').
        // También nos traemos los datos del producto y de la persona que lo vendió.
        $compras = Compraventa::with('producto', 'vendedor')
            ->where('id_comprador', $userId)
            ->get();

        // 5. Enviamos los datos: Metemos todas esas variables en una sola respuesta en formato JSON.
        // Así, el frontend tiene todo lo necesario para lo que necesite mostrar por pantalla.
        return response()->json([
            'productos' => $productos,
            'ventas' => $ventas,
            'compras' => $compras,
        ]);
    }
}
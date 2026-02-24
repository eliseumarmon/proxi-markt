<?php
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraVentaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PuntoEntregaController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MensajesController;
use App\Http\Controllers\ValoracionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Rutas de puntos de entrega

Route::get('/puntos/{punto}/productos', [ProductoController::class, 'obtenerProductosPunto']);

// Rutas de productos públicas

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);


// Rutas de categorías

Route::get('/categorias', [CategoriaController::class, 'index']);
Route::post("/categorias", [CategoriaController::class, "store"]); // se debería envolver en autenticación basada en rol administrador

// Rutas de usuarios públicas

Route::post('/register', [AuthController::class, 'createUser'])
    ->name('register');
Route::post('/login', [AuthController::class, 'loginUser'])
    ->name('login');

// Rutas protegidas

Route::middleware('auth:sanctum')->group(function () {

    // Rutas productos protegidas

    Route::apiResource('productos', ProductoController::class)->only([
        'store', 'update', 'destroy'
    ]);
    
    // siguiendo estructura apirest que se lee: de los usuarios, el que conincida con este id, dame los productos
    // se mantiene el método en el controlador de productos porque es lo que se desea obtener
    Route::get('/usuarios/{usuario}/productos', [ProductoController::class, 'productosPorUsuario']); 
    
    // Rutas para puntos protegidas

    Route::get('/puntos_radio/{radio}', [PuntoEntregaController::class, 'puntosRadio']);
    Route::get('/usuarios/{usuario}/puntos', [PuntoEntregaController::class, 'puntosPorVendedor']);
    Route::post('/puntos', [PuntoEntregaController::class, 'store']);
    Route::delete('/puntos/{id}', [PuntoEntregaController::class, 'destroy']);

    // Rutas para usuarios protegidas

    Route::get('/datosuser', [UserController::class, 'unUsuario']);
    Route::put('/usuarios/{usuario}/ubicacion', [UserController::class, 'updateLocation']);

    // Rutas de compraventa

    Route::post("/compraventa/{producto}", [CompraVentaController::class, 'store']);
    Route::get('/mis-compras', [CompraVentaController::class, 'misCompras']);
    Route::get('/mis-ventas', [CompraVentaController::class, 'misVentas']);
    Route::get('/mis-comandas', [CompraVentaController::class, 'misComandas']);
    Route::put("/mis-comandas/{compraventa}", [CompraVentaController::class, 'actualizarEstado']);

    // Rutas de chat

    Route::get('/mis-chats', [ChatController::class, 'index']);
    Route::get('/chat/{id}',[ChatController::class, 'show']);

    // Rutas de mensajes

    Route::post('/enviar-mensaje', [MensajesController::class, 'store']);

    // Rutas de valoraciones

    Route::get('/valoraciones', [ValoracionController::class, 'index']);
    Route::post('/valoraciones/{compraventa}', [ValoracionController::class, 'store']);

    // Ruta de notificaciones
    Route::put('/chats/{id}/leer', [App\Http\Controllers\ChatController::class, 'marcarLeido']);

    // Ruta de Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
});


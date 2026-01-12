<?php
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

//Rutas de productos
// Obtener todos los productos (Tienda/Mapa)
Route::get('/productos', [ProductoController::class, 'index']);

// Obtener un producto específico por ID
Route::get('/productos/{id}', [ProductoController::class, 'show']);

// Crear un producto (Agricultor)
Route::post('/productos', [ProductoController::class, 'store']);

// Actualizar stock o precio (Agricultor)
Route::put('/productos/{id}', [ProductoController::class, 'update']);

//Rutas de usuarios
Route::get('/users', [UserController::class, 'index']);
Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'login']);

<?php
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PuntoEntregaController;
use App\Http\Controllers\CategoriasController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'createUser'])
    ->name('register');
Route::post('/login', [AuthController::class, 'loginUser'])
    ->name('login');

Route::get('/categorias', [CategoriasController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/puntosuser', [PuntoEntregaController::class, 'puntosPorVendedor']);
    Route::post('/insertarpunto', [PuntoEntregaController::class, 'store']);
    Route::get('/datosuser', [UserController::class, 'unUsuario']);
    Route::put('/ubicacionusuario', [UserController::class, 'updateLocation']);
    Route::delete('/deletepunto/{id}', [PuntoEntregaController::class, 'destroy']);
    Route::post('/publicarproducto', [ProductoController::class, 'store']);
});
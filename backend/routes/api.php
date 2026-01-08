<?php
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/usuarios', [UserController::class, 'index']); 

Route::post('/usuarios', [UserController::class, 'store']);
?>
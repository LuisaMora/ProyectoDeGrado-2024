<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:administrador')->group(function () {
    // Rutas para el administrador
});

Route::middleware('auth:propietario')->group(function () {
    // Rutas para el propietario
});

Route::middleware('auth:empleado')->group(function () {
    // Rutas para el empleado
});

Route::get('example', function () {
    return 'Hello World';
});

Route::post('/login', AuthController::class . '@login');
Route::post('/register', AuthController::class . '@register');


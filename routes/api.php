<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', 'App\Http\Controllers\AuthController@logout');

    Route::middleware('propietario')->group(function () {
        Route::post('/menu/platillo', 'App\Http\Controllers\PlatilloController@store');
        Route::post('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@update');
        Route::get('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@show');
        Route::delete('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@destroy'); // Corregido el nombre de la ruta
    });
    
    Route::middleware('administrador')->group(function () {
        Route::get('/prueba_admin', function () {
            return response()->json(['message' => 'Bienvenido administrador']);
        });
    });
    
    Route::middleware('empleado')->group(function () {
        Route::get('/prueba_empleado', function () {
            return response()->json(['message' => 'Bienvenido empleado','auth' => auth()->user()]);
        });
    });

    Route::middleware('propietarioOempleado')->group(function () {
        Route::get('/menu/platillo', 'App\Http\Controllers\PlatilloController@index');
    });
    
});


Route::post('/login', 'App\Http\Controllers\AuthController@login');

Route::get('/prohibido', function () {
    return response()->json([
        'message' => 'No tienes permiso para acceder a esta ruta',
    ], 403);
})->name('prohibido');
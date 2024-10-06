<?php

use App\Http\Controllers\PreRegistroController;
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
    Route::get('/logout', 'App\Http\Controllers\Auth\AuthController@logout');

    Route::middleware('propietario')->group(function () {
        Route::get('/menu/platillos/{id}', 'App\Http\Controllers\PlatilloController@index');
        Route::post('/menu/platillo', 'App\Http\Controllers\PlatilloController@store');
        Route::post('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@update');
        Route::get('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@show');
        Route::delete('/menu/platillo/{id}', 'App\Http\Controllers\PlatilloController@destroy'); // Corregido el nombre de la ruta
   
        
        Route::post('/menu/categoria', 'App\Http\Controllers\CategoriaController@store');
        Route::post('/menu/categoria/{id}', 'App\Http\Controllers\CategoriaController@update');
        Route::get('/menu/categoria/{id}', 'App\Http\Controllers\CategoriaController@show');
        Route::delete('/menu/categoria/{id}', 'App\Http\Controllers\CategoriaController@destroy');

        Route::get('/menu', 'App\Http\Controllers\MenuController@index');
        Route::post('/menu', 'App\Http\Controllers\MenuController@storeMenu');

        Route::post('/menu/generar/qr', 'App\Http\Controllers\MenuController@generateQr');
        
        Route::get('/restaurante', 'App\Http\Controllers\RestauranteController@show');

        Route::post('/reporte/pedidos', 'App\Http\Controllers\ReporteController@getReporte');
        
        Route::post('/actualizar/datos-personales', 'App\Http\Controllers\Auth\AuthController@updateDatosPersonales');
        Route::get('/datos-personales', 'App\Http\Controllers\Auth\AuthController@show');
        Route::post('/restablecer-contrasenia', 'App\Http\Controllers\auth\AuthController@restablecerContrasenia');
    });
    
    Route::middleware('administrador')->group(function () {
        Route::get('/prueba_admin', function () {
            return response()->json(['message' => 'Bienvenido administrador']);
        });
        Route::get('/pre-registros', 'App\Http\Controllers\PreRegistroController@index');
        Route::put('/pre-registro/confirmar', 'App\Http\Controllers\PreRegistroController@confirmar');
        Route::get('/propietarios', 'App\Http\Controllers\Auth\CuentaUsuarioController@propietarios');

        Route::put('/propietario/dar-baja/{id_usuario}', function($id_usuario) {
            return app('App\Http\Controllers\Auth\CuentaUsuarioController')
                        ->cambiarEstadoPropietario($id_usuario, false);
        });
        
        Route::put('/propietario/dar-alta/{id_usuario}', function($id_usuario) {
            return app('App\Http\Controllers\Auth\CuentaUsuarioController')
                        ->cambiarEstadoPropietario($id_usuario, true);
        });
    }); 
    
    // Route::middleware('empleado')->group(function () {
    //     Route::get('/menu/pedido', 'App\Http\Controllers\PlatilloController@index');
    //     Route::get('/menu/pedido/platillos', 'App\Http\Controllers\PlatilloController@platillosDisponibles');
    //     Route::get('/prueba_empleado', function () {
    //         return response()->json(['message' => 'Bienvenido empleado','auth' => auth()->user()]);
    //     });
    //     Route::get('/pedidos', 'App\Http\Controllers\PedidoController@index');
    // });
    //por ahora solo existe mesero , cocinero y cajero
    Route::middleware('empleado:mesero,cajero')->group(function () {
        Route::post('/pedido', 'App\Http\Controllers\Pedido\PedidoController@store');
        Route::get('/menu/pedido/platillos', 'App\Http\Controllers\PlatilloController@platillosDisponibles');

        Route::post('/pedido', 'App\Http\Controllers\Pedido\PedidoController@store');
        Route::delete('/pedidos/{id}', 'App\Http\Controllers\PedidoController@destroy');
        Route::get('/prueba_empleado', function () {
            return response()->json(['message' => 'Bienvenido empleado','auth' => auth()->user()]);
        });
        Route::get('/pedidos/{idEmpleado}/{idRestaurante}', 'App\Http\Controllers\PedidoController@index');
    });


    Route::middleware('empleado:cajero,mesero,cocinero')->group(function () {
        Route::put('/plato-pedido/estado', 'App\Http\Controllers\Pedido\CambiarEstadoController@update');
        Route::get('/menu/pedido', 'App\Http\Controllers\PlatilloController@index');
        Route::get('/notificaciones', 'App\Http\Controllers\NotificacionController@obtenerNotificaciones');
        Route::get('/notificaciones/cantidad', 'App\Http\Controllers\NotificacionController@obtenerNotificacionesCantidad');
        Route::put('/notificaciones/leidas', 'App\Http\Controllers\NotificacionController@marcarComoLeida');
        Route::get('/pedidos/{idEmpleado}/{idRestaurante}', 'App\Http\Controllers\Pedido\PedidoController@index');
        Route::get('/pedido/platos/{idPedido}/{idRestaurante}', 'App\Http\Controllers\Pedido\PedidoController@showPlatillos');
        Route::post('/cuenta/store/{idRestaurante}', 'App\Http\Controllers\Pedido\CuentaController@store');
        Route::post('/cuenta/close/{id}', 'App\Http\Controllers\Pedido\CuentaController@close');
    });

    Route::middleware('empleado:cajero')->group(function () {
        Route::get('/show/cuenta/{id}', 'App\Http\Controllers\Pedido\CuentaController@show');
    });
    
    Route::middleware('propietarioOempleado')->group(function () {
        Route::get('/menu/categoriaRestaurante/{id}', 'App\Http\Controllers\CategoriaController@index');
        Route::get('/menu/categoria', 'App\Http\Controllers\CategoriaController@index');
        Route::get('/restaurante/mesas', 'App\Http\Controllers\MesaController@index');
        Route::post('/empleado', 'App\Http\Controllers\EmpleadoController@store');

    });
    
});

Route::get('/menu/{id}', 'App\Http\Controllers\MenuController@show');

Route::post('/login', 'App\Http\Controllers\Auth\AuthController@login');

Route::post('/pre-registro', 'App\Http\Controllers\PreRegistroController@store');

Route::post('solicitar-cambio-contrasenia', 'App\Http\Controllers\auth\AuthController@solicitarCambioContrasenia');
Route::post('/restablecer-contrasenia-olvidada', 'App\Http\Controllers\auth\AuthController@restablecerContrasenia');


Route::get('/prohibido', function () {
    return response()->json([
        'message' => 'No tienes permiso para acceder a esta ruta',
    ], 403);
})->name('prohibido'); 

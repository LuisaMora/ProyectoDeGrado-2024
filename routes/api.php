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
    Route::get('/logout', 'App\Http\Controllers\Auth\AuthenticationController@logout');

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

        Route::get('/menu/datos/{idRestaurante}', 'App\Http\Controllers\MenuController@index');
        Route::post('/menu', 'App\Http\Controllers\MenuController@storeMenu');

        Route::post('/menu/generar/qr', 'App\Http\Controllers\MenuController@generateQr');
        
        Route::post('/reporte/pedidos', 'App\Http\Controllers\ReporteController@getReporte');
        
        Route::post('/actualizar/datos-personales', 'App\Http\Controllers\Auth\UserDetailsController@updateUserDetails')
        ->defaults('esPropietario', true);
        Route::get('/datos-personales', 'App\Http\Controllers\Auth\AuthController@show'); // borrar esto despiues de probar con front
        Route::post('/restablecer-contrasenia', 'App\Http\Controllers\Auth\AuthController@restablecerContrasenia');
        Route::get('/empleados', 'App\Http\Controllers\Auth\UserManagementController@empleados');

        Route::put('/empleado/dar-baja/{id_usuario}', 'App\Http\Controllers\Auth\UserManagementController@cambiarEstadoUsuario')
        ->defaults('estado', false)
        ->defaults('tipo', 'empleado');

        Route::put('/empleado/dar-alta/{id_usuario}', 'App\Http\Controllers\Auth\UserManagementController@cambiarEstadoUsuario')
        ->defaults('estado', true)
        ->defaults('tipo', 'empleado');

        Route::post('/empleado', 'App\Http\Controllers\EmpleadoController@store');
    });
    
    Route::middleware('administrador')->group(function () {
        Route::get('/prueba_admin', function () {
            return response()->json(['message' => 'Bienvenido administrador']);
        });
        Route::get('/pre-registros', 'App\Http\Controllers\PreRegistroController@index');
        Route::put('/pre-registro/confirmar', 'App\Http\Controllers\PreRegistroController@confirmar');
        Route::get('/propietarios', 'App\Http\Controllers\Auth\UserManagementController@propietarios');

        Route::put('/propietario/dar-baja/{id_usuario}', 'App\Http\Controllers\Auth\UserManagementController@cambiarEstadoUsuario')
        ->defaults('estado', false)
        ->defaults('tipo', 'propietario');
        
        Route::put('/propietario/dar-alta/{id_usuario}', 'App\Http\Controllers\Auth\UserManagementController@cambiarEstadoUsuario')
        ->defaults('estado', true)
        ->defaults('tipo', 'propietario');
    }); 

    Route::middleware('empleado:mesero,cajero')->group(function () {
        Route::post('/pedido', 'App\Http\Controllers\Pedido\PedidoController@store');
        Route::get('/menu/pedido/platillos/{idRestauante}', 'App\Http\Controllers\PlatilloController@platillosDisponibles');

        Route::post('/pedido', 'App\Http\Controllers\Pedido\PedidoController@store');
        // Route::delete('/pedidos/{id}', 'App\Http\Controllers\PedidoController@destroy');
        Route::get('/prueba_empleado', function () {
            return response()->json(['message' => 'Bienvenido empleado','auth' => auth()->user()]);
        });
    });


    Route::middleware('empleado:cajero,mesero,cocinero')->group(function () {
        Route::get('/menu/pedido', 'App\Http\Controllers\PlatilloController@index');
        Route::get('/notificaciones', 'App\Http\Controllers\NotificacionController@obtenerNotificaciones');
        Route::get('/notificaciones/cantidad', 'App\Http\Controllers\NotificacionController@obtenerNotificacionesCantidad');
        Route::put('/notificaciones/leidas', 'App\Http\Controllers\NotificacionController@marcarComoLeida');
        Route::get('/pedidos/{idEmpleado}/{idRestaurante}', 'App\Http\Controllers\Pedido\PedidoController@index');
        
        Route::post('/actualizar/datos-empleado', 'App\Http\Controllers\Auth\UserDetailsController@updateUserDetails')
        ->defaults('esPropietario', false);
        
        Route::post('/cuenta/store/{idRestaurante}', 'App\Http\Controllers\Pedido\CuentaController@store');
    });

    Route::middleware('empleado:cajero')->group(function () {
        Route::get('/cuentas/abiertas/{idRestaurante}', 'App\Http\Controllers\Pedido\CuentaController@index');
        Route::get('/cuentas/cerradas/{idRestaurante}', 'App\Http\Controllers\Pedido\CuentaController@showCerradas');
        Route::get('/show/cuenta/{id}', 'App\Http\Controllers\Pedido\CuentaController@show');
        Route::post('/cuenta/close/{id}', 'App\Http\Controllers\Pedido\CuentaController@close');
    });

    Route::middleware('empleado:cocinero')->group(function () {
       Route::get('/pedido/platos/{idPedido}/{idRestaurante}', 'App\Http\Controllers\Pedido\PedidoController@showPlatillos');
       Route::put('/plato-pedido/estado', 'App\Http\Controllers\Pedido\CambiarEstadoController@update');
    });
    
    Route::middleware('propietarioOempleado')->group(function () {
        Route::get('/menu/categoriaRestaurante/{id}', 'App\Http\Controllers\CategoriaController@index');
        // Route::get('/menu/categoria', 'App\Http\Controllers\CategoriaController@index'); Estoy Borrando esto por que esta duplicado
        Route::get('/restaurante/mesas/{idRestaurante}', 'App\Http\Controllers\MesaController@index');
        Route::get('/datos-personales', 'App\Http\Controllers\Auth\UserDetailsController@getUserDetails');
        Route::post('/restablecer-contrasenia', 'App\Http\Controllers\Auth\AuthenticationController@restablecerContrasenia');
        Route::get('/restaurante', 'App\Http\Controllers\RestauranteController@show');
    });
    
});

Route::get('/menu/{id}', 'App\Http\Controllers\MenuController@show');

Route::post('/login', 'App\Http\Controllers\Auth\AuthenticationController@login');

Route::post('/pre-registro', 'App\Http\Controllers\PreRegistroController@store');

Route::post('solicitar-cambio-contrasenia', 'App\Http\Controllers\Auth\AuthenticationController@solicitarCambioContrasenia');
Route::post('/restablecer-contrasenia-olvidada', 'App\Http\Controllers\Auth\AuthenticationController@restablecerContrasenia');


Route::get('/prohibido', function () {
    return response()->json([
        'message' => 'No tienes permiso para acceder a esta ruta',
    ], 403);
})->name('prohibido'); 

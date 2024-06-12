<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Notificacion;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificacionController extends Controller
{
    public static function obtenerNotificaciones(Request $request)
    {
        $user = auth()->user();
        $validarDatos = Validator::make($request->all(), [
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'message' => $validarDatos->errors()], 400);
        }
        if ($user) {
            $idTipoEmpleado = Empleado::select('id_rol', 'id')
                ->where('id_usuario', $user->id)
                ->first();

            if ($idTipoEmpleado) {
                if ($idTipoEmpleado->id_rol == 1) {
                    $listaPedidos = Pedido::select('id')
                        ->where('id_empleado', $idTipoEmpleado->id)
                        ->get();
                        // print_r($listaPedidos);
                    $notificaciones = Notificacion::orderBy('created_at', 'desc')
                        ->where('id_restaurante', $request->id_restaurante)
                        ->whereIn('id_pedido', $listaPedidos)
                        ->get();
                } else {
                    $notificaciones = Notificacion::orderBy('created_at', 'desc')
                        ->where('id_restaurante', $request->id_restaurante)
                        ->get();
                }
                foreach ($notificaciones as $notificacion) {
                    $notificacion->creado_hace = $notificacion->created_at->diffForHumans();
                }
                return response()->json(['status' => 'success', 'notificaciones' => $notificaciones], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No tienes permisos para acceder a esta información'], 403);
            }
        }
    }

    public function obtenerNotificacionesCantidad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }
        $cantidad = $request->cantidad;
        $idRestaurante = $request->id_restaurante;
        $user = auth()->user();
        if ($user) {
            $notificaciones = Notificacion::select('id', 'id_creador', 'tipo', 'titulo', 'mensaje', 'created_at', 'read_at')
                ->orderBy('created_at', 'desc')
                ->where('id_restaurante', $idRestaurante)
                ->take($cantidad)
                ->get();
            foreach ($notificaciones as $notificacion) {
                $notificacion->creado_hace = $notificacion->created_at->diffForHumans();
            }
            return response()->json(['status' => 'success', 'notificaciones' => $notificaciones], 200);
        } else {
            // Manejar el caso en el que no hay un usuario autenticado
            return response()->json(['status' => 'error', 'message' => 'Usuario no autenticado'], 401);
        }
    }

    private function getNotificacion($idRestaurante, $cantidad = 0){
        $user = auth()->user();
        $idTipoEmpleado = Empleado::select('id_rol', 'id')
                ->where('id_usuario', $user->id)
                ->first();
    
        if ($idTipoEmpleado) {
            if ($idTipoEmpleado->id_rol == 1) {
                $listaPedidos = Pedido::select('id')
                    ->where('id_empleado', $idTipoEmpleado->id)
                    ->pluck('id');  // Cambiar get() por pluck('id') para obtener una lista de IDs
    
                $notificacionesQuery = Notificacion::orderBy('created_at', 'desc')
                    ->where('id_restaurante', $idRestaurante)
                    ->whereIn('id_pedido', $listaPedidos);
            } else {
                $notificacionesQuery = Notificacion::orderBy('created_at', 'desc')
                    ->where('id_restaurante', $idRestaurante);
            }
    
            // Agregar condicional para aplicar 'take' solo si cantidad es mayor a 0
            if ($cantidad > 0) {
                $notificacionesQuery->take($cantidad);
            }
    
            $notificaciones = $notificacionesQuery->get();
    
            foreach ($notificaciones as $notificacion) {
                $notificacion->creado_hace = $notificacion->created_at->diffForHumans();
            }
            
            return response()->json(['status' => 'success', 'notificaciones' => $notificaciones], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'No tienes permisos para acceder a esta información'], 403);
        }
    }
    
}

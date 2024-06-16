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
    public  function obtenerNotificaciones(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'message' => $validarDatos->errors()], 400);
        }
        return $this->getNotificacion($request->id_restaurante);
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
      return $this->getNotificacion($idRestaurante, $cantidad);
    }

    public function marcarComoLeida(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'id_notificaciones' => 'required|array',
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'message' => $validarDatos->errors()], 400);
        }
        $userId = auth()->user()->id;
        $notificaciones = Notificacion::whereIn('id', $request->id_notificaciones)
            ->where('id_restaurante', $request->id_restaurante)
            ->where('id_creador', $userId)
            ->update(['read_at' => now()]);
        if ($notificaciones == 0) {
            return response()->json(['status' => 'error', 'message' => 'No se encontraron notificaciones para marcar como leídas'], 404);
        }
        return response()->json(['status' => 'success', 'message' => 'Notificaciones marcadas como leídas'], 200);
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

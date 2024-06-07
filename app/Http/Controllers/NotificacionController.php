<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
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
            $notificaciones = Notificacion::select('id', 'tipo', 'titulo', 'mensaje', 'created_at', 'read_at')
                ->orderBy('created_at', 'desc')
                ->where('id_restaurante', $request->id_restaurante)
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
            $notificaciones = Notificacion::select('id', 'tipo', 'titulo', 'mensaje', 'created_at', 'read_at')
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
}

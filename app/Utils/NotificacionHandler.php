<?php

namespace App\Utils;

use App\Events\PedidoCompletado;
use App\Events\PedidoCreado;
use App\Events\PedidoEliminado;
use App\Events\PedidoEnPreparacion;
use App\Events\PedidoServido;
use App\Models\Notificacion;
use App\Events\Notificacion as NotificacionEvent;

class NotificacionHandler
{
    public static function enviarNotificacion($pedido, $idEstado, $idRestaurante, $nombreMesa, $idPedidoEmpleado)
    {
        $user = auth()->user();
        if ($user) {
            $mensaje = '';
            $accion = '';

            switch ($idEstado) {
                case 1:
                    // Enviar notificación de pedido en espera
                    PedidoCreado::dispatch($idRestaurante, $pedido);
                    $accion = 'creó';
                    $mensaje = 'Nuevo pedido de la '.strtoupper($nombreMesa).'.';
                    break;
                case 2:
                    // Enviar notificación de pedido en preparación
                    PedidoEnPreparacion::dispatch($pedido->id, $idRestaurante);
                    $accion = 'puso en preparación';
                    $mensaje = 'Pedido de la '.strtoupper($nombreMesa).' en preparación.';
                    break;
                case 3:
                    // Enviar notificación de pedido listo para servir
                    event(new PedidoCompletado($pedido->id, $idRestaurante));
                    $accion = 'completó';
                    $mensaje = 'Se completó el pedido de la '.strtoupper($nombreMesa).'.';
                    break;
                case 4:
                    // Enviar notificación de pedido servido
                    event(new PedidoServido($pedido->id, $idRestaurante));
                    $accion = 'sirvió';
                    $mensaje = 'Se sirvió el pedido de la '.strtoupper($nombreMesa).'.';
                    break;
                case 5:
                    // Enviar notificación de pedido cancelado
                    event(new PedidoEliminado($pedido->id, $idRestaurante));
                    $accion = 'canceló';
                    $mensaje = 'Cancelación del pedido de la '.strtoupper($nombreMesa).'.';
                    break;
            }

            // Verificar que $mensaje no esté vacío antes de crear la notificación
            if (!empty($mensaje) && $idEstado != 1) {
                $notificacion = new Notificacion();
                $notificacion->id_pedido = $pedido->id;
                $notificacion->id_creador = $user->id;
                $notificacion->id_restaurante = $idRestaurante;
                // ucfirst convierte la primera letra de la cadena a mayúscula seguido de un espacio y accion
                $notificacion->titulo = ucfirst($user->nombre).' '.$accion.' un pedido';
                $notificacion->tipo = 'pedido';
                $notificacion->mensaje = $mensaje;
                if($accion == 'creó'){
                    $notificacion->read_at = now();
                }
                $notificacion->save();
                $creado_hace = $notificacion->created_at->diffForHumans();
                NotificacionEvent::dispatch($notificacion, $idRestaurante, $creado_hace, $idPedidoEmpleado);
            } 
        } else {
            // Manejar el caso en el que no hay un usuario autenticado
            return response()->json(['status' => 'error', 'message' => 'Usuario no autenticado'], 401);
        }
    }

}

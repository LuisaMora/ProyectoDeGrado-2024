<?php

namespace App\Utils;

use App\Events\PedidoCompletado;
use App\Events\PedidoCreado;
use App\Events\PedidoEliminado;
use App\Events\PedidoEnPreparacion;
use App\Events\PedidoServido;
use App\Models\User;
use App\Models\Notificacion;

class NotificacionHandler
{
    public static function enviarNotificacion($idPedido, $idEstado, $idRestaurante)
    {
        $user = auth()->user();
        if ($user) {
            $user = User::find($user->id); // Asegurarse de que se utiliza el ID correcto del usuario autenticado
        
            if ($user) {
                $mensaje = '';
        
                switch ($idEstado) {
                    case 1:
                        // Enviar notificación de pedido en espera
                        PedidoCreado::dispatch($idRestaurante, $idPedido);
                        $mensaje = 'Pedido en espera';
                        break;
                    case 2:
                        // Enviar notificación de pedido en preparación
                        PedidoEnPreparacion::dispatch($idPedido, $idRestaurante);
                        $mensaje = 'Pedido en preparación';
                        break;
                    case 3:
                        // Enviar notificación de pedido listo para servir
                        event(new PedidoCompletado($idPedido, $idRestaurante));
                        $mensaje = 'Pedido listo para servir';
                        break;
                    case 4:
                        // Enviar notificación de pedido servido
                        event(new PedidoServido($idPedido, $idRestaurante));
                        $mensaje = 'Pedido servido';
                        break;
                    case 5:
                        // Enviar notificación de pedido cancelado
                        event(new PedidoEliminado($idPedido, $idRestaurante));
                        $mensaje = 'Pedido cancelado';
                        break;
                }
        
                // Verificar que $mensaje no esté vacío antes de crear la notificación
                if (!empty($mensaje)) {
                    $notificacion = new Notificacion();
                    $notificacion->id_usuario = $user->id;
                    $notificacion->tipo = 'pedido';
                    $notificacion->mensaje = $mensaje;
                    $notificacion->save();
        
                    print_r('Notificación enviada');
                    print_r($notificacion);
                } else {
                    // Manejar el caso en el que $mensaje esté vacío
                    print_r('No se generó un mensaje de notificación');
                }
            } else {
                // Manejar el caso en el que no se encuentra el usuario por su ID
                return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
            }
        } else {
            // Manejar el caso en el que no hay un usuario autenticado
            return response()->json(['status' => 'error', 'message' => 'Usuario no autenticado'], 401);
        }
    }
}
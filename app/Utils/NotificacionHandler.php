<?php

namespace App\Utils;

use App\Events\PedidoCompletado;
use App\Events\PedidoCreado;
use App\Events\PedidoEliminado;
use App\Events\PedidoEnPreparacion;
use App\Events\PedidoServido;
use App\Models\User;
use App\Notifications\Notificacion;

class NotificacionHandler
{
    public static function enviarNotificacion($idPedido, $idEstado, $idRestaurante)
    {
        //segun el numero de estado se envia una notificacion, hay el estado 1 2 3 4 y 5 y disparar un evento
        $user = auth()->user();
        $user = User::find($user->user_id);
        
        switch ($idEstado) {
            case 1:
                //enviar notificacion de pedido en espera
                PedidoCreado::dispatch($idRestaurante, $idPedido);
                $message = 'Pedido en espera';
                break;
            case 2:
                print_r('Pedido en preparacion');
                //enviar notificacion de pedido en preparacion
                PedidoEnPreparacion::dispatch($idPedido, $idRestaurante);
                $message = 'Pedido en preparacion';
                break;
            case 3:
                //enviar notificacion de pedido listo para servir
                event(new PedidoCompletado($idPedido, $idRestaurante));
                $message = 'Pedido listo para servir';
                break;
            case 4:
                //enviar notificacion de pedido servido
                event(new PedidoServido($idPedido, $idRestaurante));
                $message = 'Pedido servido';
                break;
            case 5:
                //enviar notificacion de pedido cancelado
                event(new PedidoEliminado($idPedido, $idRestaurante));
                $message = 'Pedido cancelado';
                break;

            $user->notify(new Notificacion($message));
        }
        

    }
}
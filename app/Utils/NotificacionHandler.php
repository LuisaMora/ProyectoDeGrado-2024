<?php

namespace App\Utils;

use App\Events\PedidoCompletado;
use App\Events\PedidoCreado;
use App\Events\PedidoEliminado;
use App\Events\PedidoEnPreparacion;
use App\Events\PedidoServido;

class NotificacionHandler
{
    public static function enviarNotificacion($idPedido, $idEstado, $idRestaurante)
    {
        //segun el numero de estado se envia una notificacion, hay el estado 1 2 3 4 y 5 y disparar un evento
        
        switch ($idEstado) {
            case 1:
                //enviar notificacion de pedido en espera
                PedidoCreado::dispatch($idRestaurante, $idPedido);
                break;
            case 2:
                print_r('Pedido en preparacion');
                //enviar notificacion de pedido en preparacion
                PedidoEnPreparacion::dispatch($idPedido, $idRestaurante);
                break;
            case 3:
                //enviar notificacion de pedido listo para servir
                event(new PedidoCompletado($idPedido, $idRestaurante));
                break;
            case 4:
                //enviar notificacion de pedido servido
                event(new PedidoServido($idPedido, $idRestaurante));
                break;
            case 5:
                //enviar notificacion de pedido cancelado
                event(new PedidoEliminado($idPedido, $idRestaurante));
                break;
        }
        

    }
}
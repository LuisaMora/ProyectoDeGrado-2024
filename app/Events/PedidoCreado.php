<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidoCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $idPedido;
    public $idRestaurante;
    /**
     * Create a new event instance.
     */
    public function __construct( $idRestaurante, $idPedido)
    {
        $this->idRestaurante = $idRestaurante;  
        $this->idPedido = $idPedido;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('pedido'.$this->idRestaurante),
        ];
    }

    public function broadcastAs()
    {
        return 'PedidoCreado';
    }
}

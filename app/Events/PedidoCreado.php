<?php

namespace App\Events;

use App\Models\Pedido;
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
    public $id;
    public $idRestaurante;
    public $mesa;
    public $tipoPedido;
    public $hora;
    public $estado;
    public $platos;
//   mesa:string;
//   tipoPedido:string;
//   hora:string;
//   estado:string;
    /**
     * Create a new event instance.
     */
    public function __construct( $idRestaurante, Pedido $pedido)
    {
        $fechaHora = $pedido->fecha_hora_pedido; // Por ejemplo, "2024-08-14 15:30:00"
        $partes = explode(' ', $fechaHora);
        $this->hora = $partes[1];
        $this->idRestaurante = $idRestaurante;  
        $this->id = $pedido->id;
        $this->mesa = $pedido->cuenta->mesa->nombre;
        $this->tipoPedido = $pedido->tipo;
        $this->estado = $pedido->estado->nombre;
        $this->platos = [];
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

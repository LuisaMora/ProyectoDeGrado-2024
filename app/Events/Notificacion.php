<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notificacion as NotificacionModel;

class Notificacion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
//     creado_hace
// : 
// "hace 1 día"
// created_at
// : 
// "2024-06-03T15:51:10.000000Z"
// id
// : 
// 1
// mensaje
// : 
// "Pedido de la MESA 1 en preparación."
// read_at
// : 
// null
// tipo
// : 
// "pedido"
// titulo
// : 
// "Virgil puso en preparación un pedido"
    public string $id;
    public string $titulo;
    public string $mensaje;
    public string $tipo;
    public string $creado_hace;
    public string $created_at;
    public string $read_at;
    private $idRestaurante;
    public function __construct(NotificacionModel $notificacion , $idRestaurante, $creado_hace)
    {
       $this->id = $notificacion->id;
         $this->titulo = $notificacion->titulo;
            $this->mensaje = $notificacion->mensaje;
            $this->tipo = $notificacion->tipo;
            $this->creado_hace = $creado_hace;
            $this->created_at = $notificacion->created_at;
            $this->read_at = $notificacion->read_at||'';
            $this->idRestaurante = $idRestaurante;
        }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('notificaciones'.$this->idRestaurante),
        ];
    }

    public function broadcastAs()
    {
        return 'Notificacion';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatoPedido extends Model
{
    use HasFactory;
    protected $table = 'plato_pedido';

    protected $fillable = [
        'id_platillo',
        'id_pedido',
        'cantidad',
        'detalle',
    ];

    public function guardarPlatillos(array $platillos, $pedido_id)
    {
        foreach($platillos as $platillo) {
        PlatoPedido::create([
            'id_platillo' => $platillo['id_platillo'],
            'id_pedido' => $pedido_id,
            'cantidad' => $platillo['cantidad'],
            'detalle' => $platillo['detalle'],
        ]);
        }
    }

    public function platillo()
    {
        return $this->belongsTo(Platillo::class, 'id_platillo');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

  
}

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
        'id_estado',
        'cantidad',
        'detalle',
    ];

    public function platillo()
    {
        return $this->belongsTo(Platillo::class, 'id_platillo');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado');
    }
}

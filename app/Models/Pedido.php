<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'id_cuenta',
        'tipo',
        'id_estado' => 1,// por defecto en espera
        'id_empleado',
        'fecha_hora_pedido',
        'monto',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta');
    }
    
    public function platos()
    {
        return $this->belongsToMany(Platillo::class, 'plato_pedido', 'id_pedido', 'id_platillo')
            ->withPivot('detalle', 'cantidad');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado'); // 'id_estado' es la clave foránea en la tabla pedidos
    }
}


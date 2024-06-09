<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'id_cuenta',
        'tipo',
        'id_empleado',
        'fecha_hora_pedido',
        'monto',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function platos()
    {
        return $this->belongsToMany(Platillo::class, 'plato_pedido', 'id_pedido', 'id_platillo')
            ->withPivot('id_estado', 'detalle', 'cantidad');
    }
}

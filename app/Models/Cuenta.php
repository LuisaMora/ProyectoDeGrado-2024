<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $fillable = [
        'id_mesa',
        'nombre_razon_social',
        'monto_total',
        'estado',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cuenta_id');
    }
    public function estadoCuentas()
    {
        return $this->hasMany(EstadoCuenta::class, 'cuenta_id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

}

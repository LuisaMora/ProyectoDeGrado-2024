<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $fillable = [
        'mesa_id',
        'nombre_razon_social',
        'monto_total',
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
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

}

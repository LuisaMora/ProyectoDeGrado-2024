<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_mesa',
        'nit',
        'monto_total',
        'estado',
        'nombre_razon_social',
        'id_restaurante'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cuenta');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

}

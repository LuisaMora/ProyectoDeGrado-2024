<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'cuentas'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Nombre de la clave primaria
    public $timestamps = true;

    protected $fillable = [
        'pedido_id',
        'mesa_id',
        'monto_total',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

}

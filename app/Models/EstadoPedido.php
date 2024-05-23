<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    use HasFactory;
    protected $table = 'estado_pedidos'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Nombre de la clave primaria
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        // Agrega más columnas si es necesario
    ];

}

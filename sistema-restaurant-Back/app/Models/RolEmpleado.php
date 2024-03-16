<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolEmpleado extends Model
{
    use HasFactory;
    protected $table = 'rol_empleados'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Nombre de la clave primaria
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        // Agrega más columnas si es necesario
    ];
}

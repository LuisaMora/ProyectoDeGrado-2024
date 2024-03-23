<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    use HasFactory;

    protected $table = 'propietarios'; // Nombre de la tabla
    protected $primaryKey = 'id'; //
    public $timestamps = true;
    
    protected $fillable = [
        'id_administrador',
        'id_restaurante',
        'id_usuario',
        'ci',
        'fecha_registro',
        'pais',
        'departamento',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;
    protected $table = 'empleados'; // Nombre de la tabla
    protected $primaryKey = 'id'; //
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_propietario',
        'fecha_nacimiento',
        'fecha_contratacion',
        'ci',
        'direccion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_user');
    }

    public function rol()
    {
        return $this->belongsTo(RolEmpleado::class, 'id_rol');
    }
}

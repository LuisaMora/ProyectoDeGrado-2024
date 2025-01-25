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
        'id_restaurante'
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function rol()
    {
        return $this->belongsTo(RolEmpleado::class, 'id_rol');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario');
    }
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_empleado');
    }
}

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

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'id_administrador');
    }

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'id_restaurante');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
    public function propietario()
    {
    return $this->hasOne(Propietario::class, 'id_usuario', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioPreRegistro extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'formulario_pre_registro';

    // Definir los campos que se pueden llenar
    protected $fillable = [
        'nombre_restaurante',
        'nit',
        'celular_restaurante',
        'correo_restaurante',
        'licencia_funcionamiento',
        'tipo_establecimiento',
        'latitud',
        'longitud',
        'nombre_propietario',
        'apellido_paterno_propietario',
        'apellido_materno_propietario',
        'cedula_identidad_propietario',
        'correo_propietario',
        'fotografia_propietario',
    ];

    // Configurar timestamps si no quieres usar created_at y updated_at
    public $timestamps = true;
}

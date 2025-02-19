<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    use HasFactory;
    
    // Nombre de la tabla
    protected $table = 'restaurantes';
    
    // Nombre de la clave primaria
    protected $primaryKey = 'id';
    
    // Los timestamps se gestionan automáticamente
    public $timestamps = true;

    // Campos asignables en masa
    protected $fillable = [
        'nombre',
        'nit',
        'latitud',
        'longitud',
        'celular',
        'correo',
        'licencia_funcionamiento',
        'tipo_establecimiento'
    ];


    // Relación con la tabla 'mesas'
    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'id_restaurante');
    }

    public function menu()
{
    return $this->hasOne(Menu::class, 'id_restaurante');
}

}

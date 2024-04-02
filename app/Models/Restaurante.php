<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    use HasFactory;
    protected $table = 'restaurantes'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Nombre de la clave primaria
    public $timestamps = true;

    protected $fillable = [
        'id_menu',
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'correo',
        'licencia_funcionamiento',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }   
}

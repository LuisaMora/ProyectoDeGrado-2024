<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menus'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Nombre de la clave primaria
    public $timestamps = true;

    protected $fillable = [
        'id_restaurante',
        'portada',
        'tema',
        'qr',
        'disponible',
    ];


    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'id_restaurante');
    }
}

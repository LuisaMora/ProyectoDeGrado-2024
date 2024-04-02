<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platillo extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_menu',
        'nombre',
        'precio',
        'imagen',
        'descripcion',
        'id_categoria',
        'disponible',
        'plato_disponible_menu'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function delete()
    {
        $this->disponible = false;
        $this->save(); 
    }
}

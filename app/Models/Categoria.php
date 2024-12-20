<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $table = 'categorias';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'imagen',
        'estado',
        'id_menu'
    ];
    public function platillos()
    {
        return $this->hasMany(Platillo::class, 'id_categoria');
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
    
}

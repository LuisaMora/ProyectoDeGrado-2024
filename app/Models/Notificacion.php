<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'id_usuario',
        'id_pedido',
        'id_restaurante',
        'titulo',
        'tipo',
        'mensaje',
        'read_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

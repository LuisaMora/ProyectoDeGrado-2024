<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'password',
        'correo',
        'nickname',
        'foto_perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function esAdministrador(): bool
    {
        $esAdmin = Usuario::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM administradores WHERE id_usuario = usuarios.id) THEN true
            ELSE false
        END AS es_admin
        ")->where('id', $this->id)->first();
        return $esAdmin->es_admin;
    }

    public function esPropietario(): bool
    {
        $esProp = Usuario::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM propietarios WHERE id_usuario = usuarios.id) THEN true
            ELSE false
        END AS es_prop
        ")->where('id', $this->id)->first();
        return $esProp->es_prop;
    }
}

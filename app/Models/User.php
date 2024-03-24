<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'usuarios';
    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    public function esPropietario(): bool
    {
        $esProp = User::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM propietarios WHERE id_usuario = usuarios.id) THEN true
            ELSE false
        END AS es_prop
        ")->where('id', $this->id)->first();
        return $esProp->es_prop;
    }

    public function esAdministrador(): bool
    {
        $esAdmin = User::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM administradores WHERE id_usuario = usuarios.id) THEN true
            ELSE false
        END AS es_admin
        ")->where('id', $this->id)->first();
        return $esAdmin->es_admin;
    }

    public function esEmpleado(): bool
    {
        $esEmp = User::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM empleados WHERE id_usuario = usuarios.id) THEN true
            ELSE false
        END AS es_emp
        ")->where('id', $this->id)->first();
        return $esEmp->es_emp;
    }
}

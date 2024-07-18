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
    // $table->string('nombre', 50);
    //         $table->string('apellido_paterno', 100);
    //         $table->string('apellido_materno', 100)->nullable();
    //         $table->string('correo', 100)->unique();
    //         $table->string('nickname', 100)->unique();
    //         $table->string('foto_perfil', 150)->nullable();
    //         $table->timestamp('email_verified_at')->nullable();
    //         $table->string('password');
    protected $table = 'usuarios';
    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'nickname',
        'foto_perfil',
        'apellido_paterno',
        'apellido_materno',

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

    public function getTipoUsuario(): string
    {
        $nameoftype = User::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM administradores WHERE id_usuario = usuarios.id) THEN 'Administrador'
            WHEN EXISTS (SELECT * FROM propietarios WHERE id_usuario = usuarios.id) THEN 'Propietario'
            WHEN EXISTS (SELECT * FROM empleados WHERE id_usuario = usuarios.id) THEN 'Empleado'
            ELSE ''
        END AS type_user
        ")->where('id', $this->id)->value('type_user');
        return $nameoftype;
    }

    public function getTipoEmpleado(): int
    {
        $type = Empleado::select('id_rol')->where('id_usuario', $this->id)->first();
        return $type->id_rol;
    }
}

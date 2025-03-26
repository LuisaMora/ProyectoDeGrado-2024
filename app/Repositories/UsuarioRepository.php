<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioRepository
{
    protected $model;

    public function __construct(User $usuario)
    {
        $this->model = $usuario;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function findByToken(string $token, $expiresAt)
    {
        return $this->model->where('reset_token', $token)
            ->where('reset_token_expires_at', '>', $expiresAt)
            ->first();
    }

    public function find(string $id)
    {
        return $this->model->find($id);
    }

    public function findBy(string $correoNick)
    {
        $user = User::where('correo', $correoNick)
            ->orWhere('nickname', $correoNick) // Asumiendo que el campo 'nickname' existe en tu modelo
            ->where('estado', true) // Asumiendo que el campo 'estado' existe en tu modelo
            ->first();
        return $user;
    }

    public function getTipoEmpleado(): int
    {
        return $this->model->find(auth()->user()->id)->getTipoEmpleado();
    }

    public function create($data): User
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            return null;
        }

        foreach ($data as $key => $value) {
            $usuario->$key = $value;
        }
        $usuario->save();
        return $usuario;
    }

}

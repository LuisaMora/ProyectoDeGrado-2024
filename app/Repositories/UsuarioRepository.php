<?php

namespace App\Repositories;

use App\Models\User;

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

    public function create(array $data)
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


    public function delete($id)
    {
        $usuario = $this->model->find($id);
        if ($usuario) {
            return $usuario->delete();
        }
        return false;
    }
}

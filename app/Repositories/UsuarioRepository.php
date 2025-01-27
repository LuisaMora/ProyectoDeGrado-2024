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

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $usuario = $this->model->find($id);
        if ($usuario) {
            $usuario->update($data);
            return $usuario;
        }
        return null;
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
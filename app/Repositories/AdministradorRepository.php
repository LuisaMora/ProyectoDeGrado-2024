<?php

namespace App\Repositories;

use App\Models\Administrador;

class AdministradorRepository
{
    protected $model;

    public function __construct(Administrador $administrador)
    {
        $this->model = $administrador;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findByUserId($id_usuario)
    {
        return $this->model->where('id_usuario', $id_usuario)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $administrador = $this->model->find($id);
        if ($administrador) {
            $administrador->update($data);
            return $administrador;
        }
        return null;
    }

    public function delete($id)
    {
        $administrador = $this->model->find($id);
        if ($administrador) {
            return $administrador->delete();
        }
        return false;
    }
}
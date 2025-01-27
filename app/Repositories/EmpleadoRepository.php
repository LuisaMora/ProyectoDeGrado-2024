<?php

namespace App\Repositories;

use App\Models\Empleado;

class EmpleadoRepository
{
    protected $model;

    public function __construct(Empleado $empleado)
    {
        $this->model = $empleado;
    }

    public function getAllFrom($id_propietario)
    {
        return $this->model->where('id_propietario', $id_propietario)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function findById($id)
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
        $empleado = $this->model->find($id);
        if ($empleado) {
            $empleado->update($data);
            return $empleado;
        }
        return null;
    }

    public function delete($id)
    {
        $empleado = $this->model->find($id);
        if ($empleado) {
            return $empleado->delete();
        }
        return false;
    }
}
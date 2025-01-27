<?php

namespace App\Repositories;

use App\Models\Propietario;

class PropietarioRepository
{
    protected $model;

    public function __construct(Propietario $propietario)
    {
        $this->model = $propietario;
    }

    public function all()
    {
        return $this->model->with('usuario')
        ->orderBy('created_at', 'desc')
        ->get();
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
        $propietario = $this->model->find($id);
        if ($propietario) {
            $propietario->update($data);
            return $propietario;
        }
        return null;
    }

    public function delete($id)
    {
        $propietario = $this->model->find($id);
        if ($propietario) {
            return $propietario->delete();
        }
        return false;
    }
}

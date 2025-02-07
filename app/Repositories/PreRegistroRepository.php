<?php

namespace App\Repositories;

use App\Models\FormularioPreRegistro;

class PreRegistroRepository
{
    protected $model;

    public function __construct(FormularioPreRegistro $preRegistro)
    {
        $this->model = $preRegistro;
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
        $preRegistro = $this->model->find($id);
        if ($preRegistro) {
            $preRegistro->update($data);
            return $preRegistro;
        }
        return null;
    }

    public function delete($id)
    {
        $preRegistro = $this->model->find($id);
        if ($preRegistro) {
            return $preRegistro->delete();
        }
        return false;
    }
}
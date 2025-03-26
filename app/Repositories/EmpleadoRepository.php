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
        return Empleado::with('usuario')->where('id_propietario', $id_propietario)
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findByUserId($id_usuario)
    {
        return $this->model->select('id', 'ci', 'fecha_nacimiento', 'fecha_contratacion', 'direccion', 'id_rol', 'id_restaurante')
        ->where('id_usuario', $id_usuario)->first();
    }

    public function create($data, int $idUsuario, int $idPropietario, int $idRestaurante): Empleado
    {

        return $this->model->create([
            'id_usuario' => $idUsuario,
            'id_rol' => $data->id_rol,
            'id_propietario' => $idPropietario,
            'fecha_nacimiento' => $data->fecha_nacimiento,
            'fecha_contratacion' => $data->fecha_contratacion,
            'ci' => $data->ci,
            'direccion' => $data->direccion,
            'id_restaurante' => $idRestaurante,
        ]);
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
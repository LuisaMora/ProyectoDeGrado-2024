<?php

namespace App\Repositories;

use App\Models\Cuenta;
use Illuminate\Http\Request;

class CuentaRepository
{
    protected $model;

    public function __construct(Cuenta $cuenta)
    {
        $this->model = $cuenta;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function obtenerOCrearCuenta(Request $data)
    {
        $cuentaActual = Cuenta::where('id_mesa', $data->id_mesa)
            ->whereNotIn('estado', ['Cancelada', 'Pagada'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($cuentaActual) {
            $fechaCuenta = $cuentaActual->created_at->toDateString();
            $fechaHoy = now()->toDateString();

            if ($fechaCuenta === $fechaHoy) {
                // Si la cuenta es de hoy, la reutilizamos
                return $cuentaActual;
            } else {
                // Si la cuenta es de un día anterior, la cerramos
                $cuentaActual->estado = 'Cancelada';
                $cuentaActual->save();
            }
        }
        // Crear una nueva cuenta porque no existe o la anterior ya se cerró
        $cuentaNueva = new Cuenta;
        $cuentaNueva->id_mesa = $data->id_mesa;
        $cuentaNueva->monto_total = 0;
        $cuentaNueva->id_restaurante = $data->id_restaurante;
        $cuentaNueva->save();

        return $cuentaNueva;
    }

    public function update($id, array $data)
    {
        $cuenta = $this->model->find($id);
        if ($cuenta) {
            $cuenta->update($data);
            return $cuenta;
        }
        return null;
    }

    public function delete($id)
    {
        $cuenta = $this->model->find($id);
        if ($cuenta) {
            return $cuenta->delete();
        }
        return false;
    }
}

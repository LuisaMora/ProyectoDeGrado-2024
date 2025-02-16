<?php

namespace App\Services;

use App\Repositories\CuentaRepository;
use Illuminate\Database\Eloquent\Collection;

class CuentaService
{
    protected $cuentaRepository;

    public function __construct(CuentaRepository $cuentaRepository)
    {
        $this->cuentaRepository = $cuentaRepository;
    }

    public function getCuentasByRestaurante(int $idRestaurante)
    {
        $cuentas = $this->cuentaRepository->getCuentasActivas($idRestaurante);

        if ($cuentas->isEmpty()) {
            throw new \Exception('No hay cuentas disponibles.');
        }

        return $this->procesarDatos($cuentas->toArray());
    }

    public function updateCuenta($idCuenta, array $data)
    {
        $cuenta = $this->cuentaRepository->findById($idCuenta);
        if (!$cuenta) {
            throw  new \Exception("No se encontró una cuenta con el ID proporcionado.", 404);
        }

        $cuentaActualizada = $this->cuentaRepository->update($cuenta->id, [
            'nombre_razon_social' => $data['razon_social'],
            'nit' => $data['nit']
        ]);

        return $cuentaActualizada;
    }

    private function procesarDatos($cuentas)
    {
        return array_map(function ($cuenta) {
            return [
                'id' => $cuenta['id'],
                'id_mesa' => $cuenta['id_mesa'],
                'nombre_mesa' => $cuenta['mesa']['nombre'],
                'estado' => $cuenta['estado'],
                'nombre_razon_social' => $cuenta['nombre_razon_social'],
                'monto_total' => $cuenta['monto_total'],
                'nit' => $cuenta['nit'],
                'platos' => collect($cuenta['pedidos'])
                    ->flatMap(fn($pedido) => collect($pedido['platos'])->map(fn($plato) => [
                        'id' => $plato['id'],
                        'nombre' => $plato['nombre'],
                        'precio' => $plato['precio_fijado'],
                        'id_pedido' => $plato['pivot']['id_pedido'],
                        'id_platillo' => $plato['pivot']['id_platillo'],
                        'cantidad' => $plato['pivot']['cantidad']
                    ]))->values()->all()
            ];
        }, $cuentas);
    }
}

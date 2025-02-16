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

    private function procesarDatos($cuentas)
    {
        $resultados = [];

        foreach ($cuentas as $cuenta) {
            // Inicializamos una nueva cuenta
            $nuevaCuenta = [
                'id' => $cuenta['id'],
                'id_mesa' => $cuenta['id_mesa'],
                'nombre_mesa' => $cuenta['mesa']['nombre'],
                'estado' => $cuenta['estado'],
                'nombre_razon_social' => $cuenta['nombre_razon_social'],
                'monto_total' => $cuenta['monto_total'],
                'nit' => $cuenta['nit'],
                'platos' => [] // Aquí guardaremos los platos de todos los pedidos
            ];

            // Iteramos sobre cada pedido de la cuenta
            foreach ($cuenta['pedidos'] as $pedido) {
                // Iteramos sobre los platos de cada pedido y los agregamos a la cuenta
                foreach ($pedido['platos'] as $plato) {
                    $nuevaCuenta['platos'][] = [
                        'id' => $plato['id'],
                        'nombre' => $plato['nombre'],
                        'precio' => $plato['precio_fijado'], // Usamos el precio guardado en 'plato_pedido'
                        'id_pedido' => $plato['pivot']['id_pedido'],
                        'id_platillo' => $plato['pivot']['id_platillo'],
                        'cantidad' => $plato['pivot']['cantidad']
                    ];
                }
            }

            // Agregamos la nueva cuenta transformada al resultado final
            $resultados[] = $nuevaCuenta;
        }

        return $resultados;
    }
}

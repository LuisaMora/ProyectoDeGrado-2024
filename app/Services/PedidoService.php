<?php

namespace App\Services;

use App\Repositories\PedidoRepository;
use Illuminate\Support\Collection;

class PedidoService
{
    protected PedidoRepository $pedidoRepository;

    public function __construct(PedidoRepository $pedidoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
    }

    public function obtenerPedidos(int $idEmpleado, int $idRestaurante, int $tipoEmpleado)
    {
        if ($tipoEmpleado == 1) {
            // Mesero: obtiene pedidos agrupados por mesa
            $pedidosPorMesa = $this->pedidoRepository->obtenerPedidosPorMesero($idEmpleado, $idRestaurante);
            return $this->transformarDatosPedido($pedidosPorMesa);
        }

        if ($tipoEmpleado == 3) {
            // Cocinero: obtiene todos los pedidos
            return $this->pedidoRepository->obtenerPedidosPorCocinero($idRestaurante)->toArray();
        }

        throw new \Exception("No tienes permisos para ver los pedidos.");
    }

    private function transformarDatosPedido($pedidosPorMesa)
    {
        $resultados = [];
        foreach ($pedidosPorMesa as $idMesa => $pedidos) {
            $primero = $pedidos->first(); // Tomar el primer pedido para obtener los datos de la cuenta y la mesa
            $cuenta = $primero->cuenta;
            $mesa = $cuenta->mesa;
            $pedidosMesa = [
                'id_cuenta' => $cuenta->id,
                'monto_total' => $cuenta->monto_total,
                'nombreMesa' => $mesa->nombre,
                'estado_cuenta' => $cuenta->estado,
                'pedidos' => $pedidos->map(function ($pedido) {
                    return [
                        'id_pedido' => $pedido->id,
                        'estado' => $pedido->estado->nombre,
                        'monto' => $pedido->monto,
                        'platos' => $pedido->platos->map(function ($plato) {
                            return [
                                'nombre' => $plato->nombre,
                                'precio_fijado' => $plato->pivot->precio_fijado,
                                'cantidad' => $plato->pivot->cantidad,
                                'detalle' => $plato->detalle,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];

            $resultados[] = $pedidosMesa;
        }

        return $resultados;
    }
    
}

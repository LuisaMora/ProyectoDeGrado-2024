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
            // return $pedidosPorMesa;
            return $this->transformarDatosPedido($pedidosPorMesa);
        }

        if ($tipoEmpleado == 3) {
            // Cocinero: obtiene todos los pedidos
            return $this->pedidoRepository->obtenerPedidosPorCocinero($idRestaurante)->toArray();
        }

        throw new \Exception("No tienes permisos para ver los pedidos.");
    }

    private function transformarDatosPedido($pedidosPorMesa) {
        $resultados = [];
    
        foreach ($pedidosPorMesa as $idMesa => $pedidos) {
            // Obtener los datos de la cuenta
            $primero = $pedidos->first(); // Tomar el primer pedido para obtener los datos de la cuenta y la mesa
    
            // Crear la estructura para cada mesa
            $pedidosMesa = [
                'id_cuenta' => $primero->cuenta->id,
                'monto_total' => $primero->cuenta->monto_total, // Asumiendo que esta propiedad está disponible
                'nombreMesa' => $primero->cuenta->mesa->nombre, // Asumiendo que la relación está disponible
                'estado_cuenta' => $primero->cuenta->estado,
                'pedidos' => [] // Inicializar el arreglo de pedidos
            ];
    
            // Iterar sobre los pedidos y transformar a la estructura deseada
            foreach ($pedidos as $pedido) {
                $pedidosMesa['pedidos'][] = [
                    'id_pedido' => $pedido->id,
                    'estado' => $pedido->estado->nombre, // Suponiendo que tienes una relación con estado
                    'platos' => [], // Inicializar el arreglo de platos
                    'monto' => $pedido->monto,
                ];
    
                // Agregar los platos al pedido
                foreach ($pedido->platos as $plato) {
                    $pedidosMesa['pedidos'][count($pedidosMesa['pedidos']) - 1]['platos'][] = [
                        'nombre' => $plato->nombre, // Suponiendo que esta propiedad existe
                        'precio_fijado' => $plato->pivot->precio_fijado, // Asegúrate de que el precio_fijado esté en la tabla pivot
                        'cantidad' => $plato->pivot->cantidad, // Asegúrate de que la cantidad esté en la tabla pivot
                        'detalle' => $plato->detalle // Asumiendo que tienes un detalle del plato
                    ];
                }
            }
    
            $resultados[] = $pedidosMesa; // Agregar el objeto de mesa al resultado final
        }
    
        return $resultados; // Retornar la estructura transformada
    }
}

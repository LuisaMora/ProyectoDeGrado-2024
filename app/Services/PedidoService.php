<?php

namespace App\Services;

use App\Repositories\CuentaRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PedidoRepository;
use App\Utils\NotificacionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    protected PedidoRepository $pedidoRepository;

    public function __construct(
        PedidoRepository $pedidoRepository,
        private CuentaRepository $cuentaRepository,
        private MesaRepository $mesaRepository,
        private NotificacionHandler $notificacionHandler
    ) {
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

    public function crearPedido($request)
    {
        DB::beginTransaction();
        try {
            $platillos_decode = json_decode($request->platillos, true);
            if (empty($platillos_decode)) {
                throw new \Exception('El campo platillos no puede estar vacío.', 400);
            }

            $cuenta = $this->cuentaRepository->obtenerOCrearCuenta($request);
            if (!$cuenta) {
                throw new \Exception('No se puede crear un pedido para una mesa con cuenta abierta.', 400);
            }

            $pedido = $this->pedidoRepository->crearPedido([
                'id_cuenta' => $cuenta->id,
                'tipo' => $request->tipo,
                'id_empleado' => $request->id_empleado,
                'id_estado' => 1,
                'fecha_hora_pedido' => now()
            ]);

            $nombreMesa = $this->mesaRepository->obtenerNombreMesa($request->id_mesa);
            $monto = $this->crearPlatillosPedido($platillos_decode, $pedido);

            $pedido->cuenta->monto_total += $monto;
            $pedido->monto = $monto;
            $pedido->save();

            $this->notificacionHandler->enviarNotificacion($pedido, 1, $request->id_restaurante, $nombreMesa, $request->id_empleado);

            DB::commit();
            return ['status' => 'success', 'pedido' => $pedido];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            // return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    private function crearPlatillosPedido(array $platillos, $pedido)
    {
        // Calcular el monto total del pedido
        $monto = 0;
        foreach ($platillos as $platillo) {
            $monto += $platillo['precio_unitario'] * $platillo['cantidad'];
        }

        $pedido->monto = $monto;
        $pedido->save();

        // Asociar los platillos al pedido usando la relación de muchos a muchos
        $pedido->platos()->attach(
            collect($platillos)->mapWithKeys(function ($platillo) {
                return [
                    $platillo['id_platillo'] => [
                        'precio_fijado' => $platillo['precio_unitario'],
                        'cantidad' => $platillo['cantidad'],
                        'detalle' => $platillo['detalle'],
                    ]
                ];
            })
        );

        $pedido->cuenta->increment('monto_total', $monto);

        return $monto;
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

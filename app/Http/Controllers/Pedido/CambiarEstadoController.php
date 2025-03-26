<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEstadoPedidoRequest;
use App\Services\PedidoService;

class CambiarEstadoController extends Controller
{

    public function __construct(private PedidoService $pedidoService)
    {
    }

    function update(UpdateEstadoPedidoRequest $request)
    {
        try {
            $idPedido = (int) $request->id_pedido;
            $idEstado = (int) $request->id_estado;
            $idRestaurante = (int) $request->id_restaurante;
            $pedidos = $this->pedidoService->cambiarEstadoPedido($idPedido, $idEstado, $idRestaurante);
            return response()->json(['status' => 'success', 'platosPedidos' => $pedidos], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }
}
<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Services\PedidoService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{

    public function __construct(private PedidoService $pedidoService, private UserService $userService)
    {
    }
    public function index(int $idEmpleado, int $idRestaurante): JsonResponse
    {
        try {
            $tipoEmpleado = $this->userService->getTipoEmpleado();
            $pedidos = $this->pedidoService->obtenerPedidos($idEmpleado, $idRestaurante, $tipoEmpleado);
            return response()->json(['status' => 'success', 'pedidos' => $pedidos], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    function showPlatillos($idPedido, $idRestaurante)
    {
        try {
            $resultado = $this->pedidoService->obtenerPlatillosDePedido($idPedido, $idRestaurante);
            return response()->json(['status' => 'success', 'platos' => $resultado['platos'], 'idPedido' => $idPedido], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
        
    }

    public function store(StorePedidoRequest $request)
    {
        try {
            $resultado = $this->pedidoService->crearPedido($request);
        return response()->json($resultado, $resultado['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], $e->getCode());
        }
        
    }

    // public function destroy($id)
    // {
    //     $pedido = Pedido::find($id);

    //     if (!$pedido) {
    //         return response()->json(['status' => 'error', 'error' => 'Pedido no encontrado.'], 404);
    //     }

    //     $cuenta = $pedido->cuenta;

    //     // Verifica el estado de la cuenta antes de eliminar el pedido
    //     if (in_array($cuenta->estado, ['Pagada', 'Cancelada'])) {
    //         return response()->json(['status' => 'error', 'error' => 'No se puede eliminar un pedido de una cuenta pagada o cancelada.'], 400);
    //     }
    //     $montoPedido = $pedido->monto;

    //     // Obtener los platos del pedido
    //     $platosPedidos = PlatoPedido::where('id_pedido', $pedido->id)->get();

    //     // Eliminar los platos asociados al pedido
    //     foreach ($platosPedidos as $platoPedido) {
    //         $platoPedido->delete();
    //     }

    //     // Eliminar el pedido
    //     $pedido->delete();

    //     // Actualizar el monto total de la cuenta
    //     $cuenta->monto_total -= $montoPedido;
    //     $cuenta->save();

    //     return response()->json(['status' => 'success', 'message' => 'Pedido eliminado correctamente.'], 200);
    // }


}

<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PlatoPedido;
use App\Utils\NotificacionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CambiarEstadoController extends Controller
{
    private $notificacionHandler;

    public function __construct()
    {
        $this->notificacionHandler = new NotificacionHandler();
    }

    function cambiarid_estadoPedido($idPedido, $id_estado)
    {
        $validate = Validator::make(['id_estado' => $id_estado], ['id_estado' => 'required|string|in:local,llevar'],[
            'id_estado.in' => 'El campo id_estado debe ser "local" o "llevar".',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        $pedido = Pedido::find($idPedido);
        if ($pedido == null) {
            return response()->json(['status' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }
        $pedido->id_estado = $id_estado;
        $pedido->save();
        return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
    }

    function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_pedido' => 'required|integer|min:1',
            'id_estado' => 'required|integer|min:1|max:5',
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        
        $idPedido = (int) $request->id_pedido;
        $idEstado = (int) $request->id_estado;
        $idRestaurante = (int) $request->id_restaurante;
        // return response()->json(['idPedido' => $idPedido, 'idEstado' => $idEstado, 'idRestaurante' => $idRestaurante], 200);
        $platosPedidos = PlatoPedido::where('id_pedido', $idPedido)->get();
        if ($platosPedidos->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Platos no encontrados'], 404);
        }
        foreach ($platosPedidos as $platoPedido) {
            $platoPedido->id_estado = $idEstado;
            $platoPedido->save();
        }
        $this->enviarNotificacion($idPedido, $idEstado, $idRestaurante);
        return response()->json(['status' => 'success', 'platosPedidos' => $platosPedidos], 200);
    }

    function enviarNotificacion($idPedido, $idEstado, $idRestaurante)
    {
        $this->notificacionHandler->enviarNotificacion($idPedido, $idEstado, $idRestaurante);
    }
}
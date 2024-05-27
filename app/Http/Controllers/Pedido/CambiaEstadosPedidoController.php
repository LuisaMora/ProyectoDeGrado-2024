<?php

namespace App\Http\Controllers\Pedido\CambiaEstadosPedido;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PlatoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CambiaEstadosPedidoController extends Controller
{
    function cambiarEstadoPedido($idPedido, $estado)
    {
        $validate = Validator::make(['estado' => $estado], ['estado' => 'required|string|in:local,llevar'],[
            'estado.in' => 'El campo estado debe ser "local" o "llevar".',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        $pedido = Pedido::find($idPedido);
        if ($pedido == null) {
            return response()->json(['status' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }
        $pedido->estado = $estado;
        $pedido->save();
        return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
    }

    function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_pedido' => 'required|integer',
            'estado' => 'required|integer|min:1|max:4',
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        
        $idPedido = $request->id_pedido;
        $estado = $request->estado;

        $platosPedidos = PlatoPedido::where('id_pedido', $idPedido)->get();
        if ($platosPedidos) {
            return response()->json(['status' => 'error', 'message' => 'Platos no encontrados'], 404);
        }
        foreach ($platosPedidos as $platoPedido) {
            $platoPedido->estado = $estado;
            $platoPedido->save();
        }

        $platoPedido->estado = $estado;
        $platoPedido->save();
        return response()->json(['status' => 'success', 'platoPedido' => $platoPedido], 200);
    }
}
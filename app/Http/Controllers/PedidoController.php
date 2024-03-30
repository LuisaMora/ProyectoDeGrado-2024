<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Pedido;
use App\Models\PlatoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    function index() {
        $pedidos = Pedido::all();
        return response()->json(['status' => 'success', 'pedidos' => $pedidos], 200);
    }

    function store(Request $request) {
        // lista de id de platillos con la cantidad
        // numero de mesa
        // id de empleado

        $validarDatos = Validator::make($request->all(), [
            'id_mesa' => 'required|integer',
            'id_empleado' => 'required|integer',
            'platillos' => 'required|array',
            'tipo' => 'required|string|in:local,llevar'
        ], [
            'tipo.in' => 'El campo tipo debe ser "local" o "llevar".',
        ]);
        if($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }
        // return response()->json(['status' => 'success', 'empleados' => $request->all()], 200);
        $cuenta = Cuenta::where('id_mesa', $request->mesa_id)->first();
        if($cuenta == null) {
            $cuenta = new Cuenta();
            $cuenta->id_mesa = $request->id_mesa;
            $cuenta->nombre_razon_social = 'Anónimo';
            $cuenta->monto_total = 0;
            $cuenta->save();
        }
        $pedido = new Pedido();
        $pedido->id_cuenta = $request->id_cuenta;
        $pedido->tipo = $request->tipo;
        $pedido->id_empleado = $request->id_empleado;
        $pedido->fecha_hora_pedido = now();
        $pedido->save();
        $platillos = $request->platillos;
        foreach($platillos as $platillo) {
            PlatoPedido::create([
                'id_platillo' => $platillo['id_platillo'],
                'id_pedido' => $pedido->id,
                'id_estado' => 1,// por defecto en espera
                'cantidad' => $platillo['cantidad'],
                'detalle' => $platillo['detalle'],
            ]);
            }
        return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
    }

}

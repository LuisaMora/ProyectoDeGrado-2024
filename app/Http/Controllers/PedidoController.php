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
        //Aqui es donde se crea la cuenta si no existe, si en algun nomenot se requiere dividir la cuenta, se debe hacer en otro metodo
        //y se debe especificar en que cuenta se va a registrar el pedido.
        $fecha_resta_dos_horas = now()->subHours(2)->format('Y-m-d H:i:s');
        $cuenta = $cuenta = Cuenta::where('id_mesa', $request->id_mesa)
        ->whereNotIn('estado', ['Cancelada', 'Pagada'])
        ->where('created_at', '>=', $fecha_resta_dos_horas)
        ->first();

        if($cuenta == null) {
            $cuenta = new Cuenta();
            $cuenta->id_mesa = $request->id_mesa;
            $cuenta->monto_total = 0;
            $cuenta->save();// se crea con un estado de abierta (1)
            //'estado IN ("Abierta", "Candelada", "PagoPendiente", "Pagada")'
        }        
        // return response()->json(['status' => 'success', 'cuenta' =>$cuenta], 200); 

        $pedido = new Pedido();
        $pedido->id_cuenta = $cuenta->id;
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

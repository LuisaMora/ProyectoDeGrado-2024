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
    function index()
    {
        $pedidos = Pedido::all();
        return response()->json(['status' => 'success', 'pedidos' => $pedidos], 200);
    }

    function store(Request $request)
    {

        $validarDatos = Validator::make($request->all(), [
            'id_mesa' => 'required|integer|min:1',
            'id_empleado' => 'required|integer:min:1',
            'platillos' => 'required|string',
            'tipo' => 'required|string|in:local,llevar'
        ], [
            'tipo.in' => 'El campo tipo debe ser "local" o "llevar".',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }
        $platillos_decode = json_decode($request->platillos, true);
        //verificar que no es un objeto vacio
        if (empty($platillos_decode)) {
            return response()->json(['status' => 'error', 'error' => 'El campo platillos no puede estar vacío.'], 400);
        }
        $cuenta = $this->obtenerOCrearCuenta($request);

        $pedido = $pedido = new Pedido();
        $pedido->id_cuenta = $cuenta->id;
        $pedido->tipo = $request->tipo;
        $pedido->id_empleado = $request->id_empleado;
        $pedido->fecha_hora_pedido = now();
        $pedido->save();

        $this->crearPlatillosPedido($platillos_decode, $pedido);

        return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
    }

    protected function obtenerOCrearCuenta(Request $request)
    {
        $fecha_resta_dos_horas = now()->subHours(2)->format('Y-m-d H:i:s');
        $cuenta = Cuenta::where('id_mesa', $request->id_mesa)
            ->whereNotIn('estado', ['Cancelada', 'Pagada'])
            ->where('created_at', '>=', $fecha_resta_dos_horas)
            ->first();

        if ($cuenta == null) {
            $cuenta = new Cuenta();
            $cuenta->id_mesa = $request->id_mesa;
            $cuenta->monto_total = 0;
            $cuenta->save();
        }

        return $cuenta;
    }

    protected function crearPlatillosPedido(array $platillos, Pedido $pedido)
    {
        $monto_total = 0;

        foreach ($platillos as $platillo) {
            PlatoPedido::create([
                'id_platillo' => $platillo['id_platillo'],
                'id_pedido' => $pedido->id,
                'id_estado' => 1,
                'cantidad' => $platillo['cantidad'],
                'detalle' => $platillo['detalle'],
            ]);

            $monto_total += $platillo['precio_unitario'] * $platillo['cantidad'];
        }

        $pedido->cuenta->monto_total += $monto_total;
        $pedido->cuenta->save();
    }
}

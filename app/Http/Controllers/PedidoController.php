<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Pedido; 
use App\Models\PlatoPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    public function index()
{
    $pedidos = Pedido::with(['cuenta.mesa','platos'])->get();
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
        $monto = $this->crearPlatillosPedido($platillos_decode, $pedido);
        

        return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
    }

    function delete($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido == null) {
            return response()->json(['status' => 'error', 'error' => 'El pedido no existe.'], 404);
        }
        // [
        //     ['estado' => 'Abierta'],
        //     ['estado' => 'Pagada'],
        //     ['estado' => 'Cancelada'],
        //     ['estado' => 'PagoPendiente']
        // ];
        $estadoCuenta = $pedido->cuenta->estado;
        if ($estadoCuenta != 1) {
            return response()->json(['status' => 'error', 'error' => 'No se puede cancelar un pedido de una cuenta pagada.'], 400);
        }
        
        $platosPedidos = PlatoPedido::where('id_pedido', $id)->get();
        foreach ($platosPedidos as $platoPedido) {
            $platoPedido->delete();
        }
        $pedido->delete();
        return response()->json(['status' => 'success', 'message' => 'Pedido cancelado.'], 200);
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
        $monto = 0;

        foreach ($platillos as $platillo) {
            $monto += $platillo['precio_unitario'] * $platillo['cantidad'];
        }
        $pedido->monto = $monto;
        $pedido->save();
        foreach ($platillos as $platillo) {
            PlatoPedido::create([
                'id_platillo' => $platillo['id_platillo'],
                'id_pedido' => $pedido->id,
                'id_estado' => 1,
                'cantidad' => $platillo['cantidad'],
                'detalle' => $platillo['detalle'],
            ]);
        }

        $pedido->cuenta->monto_total += $monto;
        $pedido->cuenta->save();
        return $monto;
    }
    
    public function destroy($id)
    {
        $pedido = Pedido::find($id); 

        if (!$pedido) {
            return response()->json(['status' => 'error', 'error' => 'Pedido no encontrado.'], 404);
        }

        $cuenta = $pedido->cuenta;

        // Verifica el estado de la cuenta antes de eliminar el pedido
        if (in_array($cuenta->estado, ['Pagada', 'Cancelada'])) {
            return response()->json(['status' => 'error', 'error' => 'No se puede eliminar un pedido de una cuenta pagada o cancelada.'], 400);
        }
        $montoPedido = $pedido->monto;

        // Obtener los platos del pedido
        $platosPedidos = PlatoPedido::where('id_pedido', $pedido->id)->get();

        // Eliminar los platos asociados al pedido
        foreach ($platosPedidos as $platoPedido) {
            $platoPedido->delete();
        }

        // Eliminar el pedido
        $pedido->delete();

        // Actualizar el monto total de la cuenta
        $cuenta->monto_total -= $montoPedido;
        $cuenta->save();

        return response()->json(['status' => 'success', 'message' => 'Pedido eliminado correctamente.'], 200);
    }


}

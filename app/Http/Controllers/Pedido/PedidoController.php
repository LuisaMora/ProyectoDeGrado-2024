<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Pedido; 
use App\Models\Mesa;
use App\Models\PlatoPedido;
use App\Models\User;
use App\Utils\NotificacionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    private $notificacionHandler;
    public function __construct()
    {
        $this->notificacionHandler = new NotificacionHandler();
    }
    public function index($idEmpleado, $idRestaurante)
    {
        $tipoEmpleado = User::find(auth()->user()->id)->getTipoEmpleado();
        if($tipoEmpleado == 1){
            $pedidos = Pedido::with(['cuenta.mesa', 'platos', 'estado'])
               ->where('id_empleado', $idEmpleado ) 
               ->whereHas('cuenta.mesa', function($query) use ($idRestaurante) {
            $query->where('id_restaurante', $idRestaurante);
           })
        ->get();
        }else if ($tipoEmpleado == 3){
            $pedidos = Pedido::with(['cuenta.mesa', 'platos', 'estado'])
            ->whereHas('cuenta.mesa', function($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })
            ->get();
        }else{
            return response()->json(['status' => 'error', 'error' => 'No tienes permisos para ver los pedidos.'], 403);
        }

        
    
        return response()->json(['status' => 'success', 'pedidos' => $pedidos], 200);
    }
   

    function store(Request $request)
    {

        $validarDatos = Validator::make($request->all(), [
            'id_mesa' => 'required|integer|min:1',
            'id_empleado' => 'required|integer:min:1',
            'platillos' => 'required|string',
            'id_restaurante' => 'required|integer',
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
        $pedido->id_estado = 1;
        $pedido->fecha_hora_pedido = now();
        $pedido->save();

        $nombreMesa = Mesa::where('id', $request->id_mesa)->first()->nombre;
        $monto = $this->crearPlatillosPedido($platillos_decode, $pedido);
        

        $this->crearPlatillosPedido($platillos_decode, $pedido);
        $this->notificacionHandler->enviarNotificacion($pedido->id, 1, $request->id_restaurante, $nombreMesa, $request->id_empleado);
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


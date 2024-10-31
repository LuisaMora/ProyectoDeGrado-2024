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
use Illuminate\Support\Facades\DB;
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
        if ($tipoEmpleado == 1) {
            $pedidosPorMesa = Pedido::with([
                'cuenta.mesa', 
                'platos', // Ahora puedes acceder a precio_fijado directamente en la relación
                'estado'
            ])
            ->whereDate('fecha_hora_pedido', now())
            ->where('id_empleado', $idEmpleado)
            ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })
            ->whereHas('cuenta', function ($query) {
                $query->where('estado', '!=', 'Pagada');
            })
            ->get()
            ->groupBy('cuenta.mesa.id'); // Agrupar pedidos por ID de mesa
        $pedidos = $this->transformarDatosPedido($pedidosPorMesa);
        } else if ($tipoEmpleado == 3) {
            $pedidos = Pedido::with(['cuenta.mesa', 'platos', 'estado'])
                ->whereDate('fecha_hora_pedido', now())
                ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                    $query->where('id_restaurante', $idRestaurante);
                })
                ->whereHas('cuenta', function ($query) {
                    $query->where('estado', '!=', 'Pagada');
                })
                ->get();
        } else {
            return response()->json(['status' => 'error', 'error' => 'No tienes permisos para ver los pedidos.'], 403);
        }
        return response()->json(['status' => 'success', 'pedidos' => $pedidos], 200);
    }



    function showPlatillos($idPedido, $idRestaurante)
    {
        $platillos = Pedido::with(['platos',])
            ->whereDate('fecha_hora_pedido', now())
            ->where('id', $idPedido)
            ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })->first();
        if ($platillos == null) {
            return response()->json(['status' => 'error', 'error' => 'El pedido no existe.'], 404);
        }
        return response()->json(['status' => 'success', 'platos' => $platillos['platos'], 'idPedido' => $idPedido], 200);
    }

    function store(Request $request)
    {
        try {
            DB::beginTransaction();
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
                DB::rollBack();
                return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
            }
            $platillos_decode = json_decode($request->platillos, true);
            //verificar que no es un objeto vacio
            if (empty($platillos_decode)) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'error' => 'El campo platillos no puede estar vacío.'], 400);
            }
            
            $cuenta = $this->obtenerOCrearCuenta($request);
            // return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
            if ($cuenta==null) {
                DB::commit();
                return response()->json(['status' => 'error', 'error' => 'No se puede crear un pedido para una mesa con cuenta abierta.'], 400);
            }
    
            $pedido = $pedido = new Pedido();
            $pedido->id_cuenta = $cuenta->id;
            $pedido->tipo = $request->tipo;
            $pedido->id_empleado = $request->id_empleado;
            $pedido->id_estado = 1;
            $pedido->fecha_hora_pedido = now();
            $pedido->save();
    
            $nombreMesa = Mesa::where('id', $request->id_mesa)->first()->nombre;
            $monto = $this->crearPlatillosPedido($platillos_decode, $pedido);
    
            $pedido->cuenta->monto_total += $monto;
    
            $pedido->monto = $monto;
            $pedido->save();
    
            
            $this->notificacionHandler->enviarNotificacion($pedido, 1, $request->id_restaurante, $nombreMesa, $request->id_empleado);
            DB::commit();
            return response()->json(['status' => 'success', 'pedido' => $pedido], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'error' => 'Error al crear el pedido.',
             'mensaje' => $th->getMessage()], 500);
        }
    }

    function delete($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido == null) {
            return response()->json(['status' => 'error', 'error' => 'El pedido no existe.'], 404);
        }
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
        $cuentas = Cuenta::where('id_mesa', $request->id_mesa)
            ->whereNotIn('estado', ['Cancelada', 'Pagada'])
            ->orderBy('created_at', 'desc')
            ->get();
        if ($cuentas->count() < 1 ) {
            $cuenta = new Cuenta();
            $cuenta->id_mesa = $request->id_mesa;
            $cuenta->monto_total = 0;
            $cuenta->save();
            $cuentas->push($cuenta);
            
        }else if ($cuentas->count() > 1) {
            $cuenta = $cuentas[1];
            $cuenta->estado = 'Cancelada';
            $cuenta->save();
            return null;
        }

        return $cuentas[0];
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
                'precio_fijado' => $platillo['precio_unitario'],
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

    private function transformarDatosPedido($pedidosPorMesa) {
        $resultados = [];
    
        foreach ($pedidosPorMesa as $idMesa => $pedidos) {
            // Obtener los datos de la cuenta
            $primero = $pedidos->first(); // Tomar el primer pedido para obtener los datos de la cuenta y la mesa
    
            // Crear la estructura para cada mesa
            $pedidosMesa = [
                'id_cuenta' => $primero->cuenta->id,
                'monto_total' => $primero->cuenta->monto_total, // Asumiendo que esta propiedad está disponible
                'nombreMesa' => $primero->cuenta->mesa->nombre, // Asumiendo que la relación está disponible
                'estado_cuenta' => $primero->cuenta->estado,
                'pedidos' => [] // Inicializar el arreglo de pedidos
            ];
    
            // Iterar sobre los pedidos y transformar a la estructura deseada
            foreach ($pedidos as $pedido) {
                $pedidosMesa['pedidos'][] = [
                    'id_pedido' => $pedido->id,
                    'estado' => $pedido->estado->nombre, // Suponiendo que tienes una relación con estado
                    'platos' => [], // Inicializar el arreglo de platos
                    'monto' => $pedido->monto,
                ];
    
                // Agregar los platos al pedido
                foreach ($pedido->platos as $plato) {
                    $pedidosMesa['pedidos'][count($pedidosMesa['pedidos']) - 1]['platos'][] = [
                        'nombre' => $plato->nombre, // Suponiendo que esta propiedad existe
                        'precio_fijado' => $plato->pivot->precio_fijado, // Asegúrate de que el precio_fijado esté en la tabla pivot
                        'cantidad' => $plato->pivot->cantidad, // Asegúrate de que la cantidad esté en la tabla pivot
                        'detalle' => $plato->detalle // Asumiendo que tienes un detalle del plato
                    ];
                }
            }
    
            $resultados[] = $pedidosMesa; // Agregar el objeto de mesa al resultado final
        }
    
        return $resultados; // Retornar la estructura transformada
    }
    

}

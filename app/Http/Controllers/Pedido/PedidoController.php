<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Models\Cuenta;
use App\Models\Pedido;
use App\Models\Mesa;
use App\Models\PlatoPedido;
use App\Models\User;
use App\Utils\NotificacionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\PedidoService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller
{
    private $notificacionHandler;
    public function __construct(private PedidoService $pedidoService, private UserService $userService)
    {
        $this->notificacionHandler = new NotificacionHandler();
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

    public function store(StorePedidoRequest $request)
    {
        try {
            $resultado = $this->pedidoService->crearPedido($request);
        return response()->json($resultado, $resultado['status'] === 'success' ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], $e->getCode());
        }
        
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
            $cuenta->id_restaurante = $request->id_restaurante;
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

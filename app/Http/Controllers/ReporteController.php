<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReporteController extends Controller
{
    public function getReporte(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_restaurante' => 'required|integer|min:1',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        $idRestaurante = $request->id_restaurante;
        //pedidos desde hace una fecha dada hasta una fecha limite
        if ($request->fecha_inicio && $request->fecha_fin) {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);
        } else {
            $fechaInicio = now()->subDays(7);

            $fechaFin = now();
        }
        // $restaurante = Restaurante::find($idRestaurante);
        $mesas = Mesa::select('id')->where('id_restaurante', $idRestaurante)->get();
        $cuentas = Cuenta::select('id')
            ->where('estado', 'Pagada')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereIn('id_mesa', $mesas)
            ->get();
        //  falta cantidad de cuentas cerradas por día
        $agrupar_pedidos = $this->agruparPedidosPorCuenta($fechaInicio, $fechaFin, $cuentas);



        $montoTotalClientesDia = DB::table('pedidos')
            ->select(DB::raw('DATE(fecha_hora_pedido) as fecha'), DB::raw('SUM(monto) as monto'))
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            ->whereIn('id_cuenta', $cuentas)
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();
        $cantidadClientePorDia = DB::table('pedidos')
            ->select(DB::raw('DATE(fecha_hora_pedido) as fecha'), DB::raw('COUNT(id) as cantidad'))
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            ->whereIn('id_cuenta', $cuentas)
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();
        $cantidadClientesPorMesa = DB::table('pedidos')
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            ->join('cuentas', 'pedidos.id_cuenta', '=', 'cuentas.id')
            ->join('mesas', 'cuentas.id_mesa', '=', 'mesas.id')
            ->whereIn('cuentas.id', $cuentas)
            ->select(
                'mesas.nombre AS mesa',
                DB::raw('COUNT(pedidos.id) AS cantidad_pedidos')
            )
            ->groupBy('mesas.nombre')
            ->orderBy('cantidad_pedidos', 'DESC')
            ->get();


        return response()->json([
            'status' => 'success',
            'montoTotalPedidosPorDia' => $montoTotalClientesDia,
            'cantidadPedidosPorDia' => $cantidadClientePorDia,
            'cantidadPedidosPorMesa' => $cantidadClientesPorMesa,
            'cuentas' => $cuentas,
            'pedidoPorCuenta' => $agrupar_pedidos
        ], 200);
    }

    function agruparPedidosPorCuenta($fechaInicio, $fechaFin, $cuentas)
    {
        $dbDriver = DB::getDriverName();

        // Selecciona la consulta según el tipo de base de datos
        $pedidosQuery = DB::table('cuentas')
            ->join('pedidos', 'pedidos.id_cuenta', '=', 'cuentas.id')
            ->join('empleados', 'empleados.id', '=', 'pedidos.id_empleado')
            ->join('usuarios', 'usuarios.id', '=', 'empleados.id_usuario')
            ->join('plato_pedido', 'plato_pedido.id_pedido', '=', 'pedidos.id')
            ->join('estado_pedidos', 'estado_pedidos.id', '=', 'pedidos.id_estado')
            ->join('platillos', 'platillos.id', '=', 'plato_pedido.id_platillo')
            ->whereIn('cuentas.id', $cuentas)
            ->whereBetween('cuentas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'cuentas.id as id_cuenta',
                'pedidos.id as id_pedido',
                'usuarios.nombre',
                'usuarios.apellido_paterno as apellido',
                'estado_pedidos.nombre as estado_pedido',
                'pedidos.monto',
                'cuentas.created_at as fecha_hora_cuenta'
            );

        if ($dbDriver === 'pgsql') {
            $pedidosQuery->selectRaw('json_agg(json_build_object(
            \'id_platillo\', platillos.id,
            \'nombre\', platillos.nombre,
            \'precio\', platillos.precio,
            \'cantidad\', plato_pedido.cantidad,
            \'detalle\', plato_pedido.detalle
        )) as platillos');
        } else {
            $pedidosQuery->selectRaw("group_concat(
            CONCAT(platillos.id, ':', platillos.nombre, ':', platillos.precio, ':', plato_pedido.cantidad, ':', plato_pedido.detalle)
            SEPARATOR '|'
        ) as platillos");
        }

        $pedidos = $pedidosQuery->groupBy(
            'cuentas.id', 
            'pedidos.id', 
            'usuarios.nombre', 
            'usuarios.apellido_paterno', 
            'estado_pedidos.nombre', 
            'pedidos.monto', 
            'cuentas.created_at'
        )->get();

        return $pedidos->map(function ($pedido) use ($dbDriver) {
            $platillos = $dbDriver === 'pgsql'
                ? json_decode($pedido->platillos)
                : $this->descomponerPlatillos($pedido->platillos);

            return [
                'id_cuenta' => $pedido->id_cuenta,
                'id_pedido' => $pedido->id_pedido,
                'empleado' => [
                    'nombre' => $pedido->nombre,
                    'apellido' => $pedido->apellido,
                ],
                'monto' => $pedido->monto,
                'fecha_hora_cuenta' => $pedido->fecha_hora_cuenta,
                'estado_pedido' => $pedido->estado_pedido,
                'platillos' => $platillos
            ];
        })->groupBy('id_cuenta');
    }

    private function descomponerPlatillos($platillosString)
    {
        $platillos = [];
        if ($platillosString) {
            $platillosData = explode('|', $platillosString);
            foreach ($platillosData as $platillo) {
                list($id, $nombre, $precio, $cantidad, $detalle) = explode(':', $platillo);
                $platillos[] = [
                    'id_platillo' => (int)$id,
                    'nombre' => $nombre,
                    'precio' => (float)$precio,
                    'cantidad' => (int)$cantidad,
                    'detalle' => $detalle,
                ];
            }
        }
        return $platillos;
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Restaurante;
use App\Models\User;
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
        //pedidos de hace una semana hasta hoy
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
            ->select(
                'mesas.nombre AS mesa',
                DB::raw('COUNT(pedidos.id) AS cantidad_pedidos')
            )
            ->groupBy('mesas.nombre')
            ->orderBy('cantidad_pedidos', 'DESC')
            ->get();


        return response()->json([
            'status' => 'success', 'montoTotalPedidosPorDia' => $montoTotalClientesDia,
            'cantidadPedidosPorDia' => $cantidadClientePorDia, 'cantidadPedidosPorMesa' => $cantidadClientesPorMesa, 'cuentas' => $cuentas,
            'pedidoPorCuenta' => $agrupar_pedidos
        ], 200);
    }

    function agruparPedidosPorCuenta($fechaInicio, $fechaFin, $cuentas)
    {
        // Obtener los pedidos con los platos relacionados
        $pedidos = DB::table('cuentas')
            ->join('pedidos', 'pedidos.id_cuenta', '=', 'cuentas.id')
            ->join('empleados', 'empleados.id', '=', 'pedidos.id_empleado')
            ->join('usuarios', 'usuarios.id', '=', 'empleados.id_usuario')
            ->join('plato_pedido', 'plato_pedido.id_pedido', '=', 'pedidos.id')
            ->join('estado_pedidos', 'estado_pedidos.id', '=', 'pedidos.id_estado')
            ->join('platillos', 'platillos.id', '=', 'plato_pedido.id_platillo')
            ->whereBetween('cuentas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'cuentas.id as id_cuenta',
                'pedidos.id as id_pedido',
                'usuarios.nombre',
                'usuarios.apellido_paterno as apellido',
                'estado_pedidos.nombre as estado_pedido',
                'pedidos.monto',
                'cuentas.created_at as fecha_hora_cuenta',
                DB::raw('json_agg(json_build_object(
            \'id_platillo\', platillos.id,
            \'nombre\', platillos.nombre,
            \'precio\', platillos.precio,
            \'cantidad\', plato_pedido.cantidad,
            \'detalle\', plato_pedido.detalle
        )) as platillos')
            )
            ->groupBy('cuentas.id', 'pedidos.id', 'usuarios.nombre', 'usuarios.apellido_paterno', 'estado_pedidos.nombre')
            ->get();

        $agrupar_pedido_por_cuenta = [];

        foreach ($pedidos as $pedido) {
            $agrupar_pedido_por_cuenta[$pedido->id_cuenta][$pedido->id_pedido] = [
                'empleado' => [
                    'nombre' => $pedido->nombre,
                    'apellido' => $pedido->apellido,
                ],
                'monto' => $pedido->monto,
                'fecha_hora_cuenta' => $pedido->fecha_hora_cuenta,
                'estado_pedido' => $pedido->estado_pedido,
                'platillos' => json_decode($pedido->platillos)
            ];
        }

        return $agrupar_pedido_por_cuenta;
    }
}

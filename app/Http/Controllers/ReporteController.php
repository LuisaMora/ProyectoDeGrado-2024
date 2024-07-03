<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Restaurante;
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
        $montoTotalPedidosPorDia = DB::table('pedidos')
            ->select(DB::raw('DATE(fecha_hora_pedido) as fecha'), DB::raw('SUM(monto) as monto'))
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            ->whereIn('id_cuenta', $cuentas)
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();
        $cantidadPedidosPorDia = DB::table('pedidos')
            ->select(DB::raw('DATE(fecha_hora_pedido) as fecha'), DB::raw('COUNT(id) as cantidad'))
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            ->whereIn('id_cuenta', $cuentas)
            ->groupBy('fecha')
            ->orderBy('fecha', 'ASC')
            ->get();
        $cantidadPedidosPorMesa =DB::table('pedidos')
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

        
        return response()->json(['status' => 'success', 'montoTotalPedidosPorDia' => $montoTotalPedidosPorDia,
        'cantidadPedidosPorDia' => $cantidadPedidosPorDia, 'cantidadPedidosPorMesa'=> $cantidadPedidosPorMesa , 'cuentas' => $cuentas], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteController extends Controller
{
    public function index(Request $request ){
        $validate = Validator::make($request->all(), [
            'id_restaurante' => 'required|integer|min:1',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        $idRestaurante = $request->id_restaurante;
        //pedidos de hace una semana hasta hoy
        $fechaInicio = now()->subDays(7);
        $fechaFin = now();
        // $restaurante = Restaurante::find($idRestaurante);
        $mesas = Mesa::select('id')->where('id_restaurante', $idRestaurante)->get();
        $cuentas = Cuenta::select('id')
        ->where('estado', 'Pagada')
        ->whereIn('id_mesa', $mesas)
        ->get();
        $pedidos = Pedido::whereIn('id_cuenta', $cuentas)
            ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
            // agrupar por cuentas
            ->get();
        
        return response()->json(['status' => 'success', 'pedidos' => $pedidos, 'cuentas' => $cuentas], 200);
    }
}

<?php

namespace App\Services;

use App\Repositories\MesaRepository;
use App\Repositories\ReporteRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class ReporteService
{

    public function __construct(private ReporteRepository $reporteRepository, private MesaRepository $mesas)
    {
    }

    public function getReporte($request)
    {
        $idRestaurante = $request->id_restaurante;
        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : now()->subDays(7);
        $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : now();

        $mesasDelRestaurante = $this->mesas->getMesasIds($idRestaurante);
        $cuentas = $this->reporteRepository->obtenerCuentasPagadas($mesasDelRestaurante, $fechaInicio, $fechaFin);
        $montoTotalClientesDia = $this->reporteRepository->obtenerMontoTotalPorDia($fechaInicio, $fechaFin, $cuentas);
        $cantidadClientePorDia = $this->reporteRepository->obtenerCantidadClientesPorDia($fechaInicio, $fechaFin, $cuentas);
        $cantidadClientesPorMesa = $this->reporteRepository->obtenerCantidadClientesPorMesa($cuentas);
        $agrupar_pedidos = $this->reporteRepository->agruparPedidosPorCuenta($fechaInicio, $fechaFin, $cuentas);

        return [
            'status' => 'success',
            'montoTotalPedidosPorDia' => $montoTotalClientesDia,
            'cantidadPedidosPorDia' => $cantidadClientePorDia,
            'cantidadPedidosPorMesa' => $cantidadClientesPorMesa,
            'cuentas' => $cuentas,
            'pedidoPorCuenta' => $agrupar_pedidos
        ];
    }
}

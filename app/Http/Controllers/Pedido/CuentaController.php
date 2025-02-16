<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCuentaRequest;
use App\Services\CuentaService;

class CuentaController extends Controller
{
    public function __construct(private CuentaService $cuentaService) {}

    public function index($id_restaurante)
    {
        try {
            $cuentas = $this->cuentaService->getCuentasByRestaurante($id_restaurante);
            return response()->json(['status' => 'success', 'cuentas' => $cuentas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreCuentaRequest $request, $idCuenta)
    {
        try {
            $cuenta = $this->cuentaService->updateCuenta($idCuenta, $request->all());
            return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function show($idCuenta)
    {
        try {
            $cuenta = $this->cuentaService->show($idCuenta);
            return response()->json(['status' => 'success', 'cuenta' => $cuenta[0]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function close($idCuenta)
    {
        try {
            $cuenta = $this->cuentaService->close($idCuenta);
            return response()->json(['status' => 'success', 'message' => 'Cuenta cerrada con éxito.', 'cuenta' => $cuenta], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function showCerradas($idRestaurante)
    {
        try {
            $cuentas = $this->cuentaService->getCuentasByRestaurante($idRestaurante, true);
            return response()->json(['status' => 'success', 'cuentas' => $cuentas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }
}

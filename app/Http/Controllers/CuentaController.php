<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 
class CuentaController extends Controller
{
    public function store(Request $request, $idRestaurante)
    {
        // Validar los datos
        $validarDatos = Validator::make($request->all(), [
            'razon_social' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:20',
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }

        // Obtener el id_mesa basado en el id_restaurante
        $mesa = Mesa::where('id_restaurante', $idRestaurante)->first();

        if (!$mesa) {
            return response()->json(['status' => 'error', 'error' => 'No se encontró una mesa para este restaurante.'], 404);
        }

        // Buscar una cuenta activa o crear una nueva
        $cuenta = $this->obtenerOCrearCuenta($mesa->id, $request);

        // Guardar razón social y NIT en la cuenta
        $cuenta->razon_social = $request->razon_social;
        $cuenta->nit = $request->nit;
        $cuenta->save();

        return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
    }

}

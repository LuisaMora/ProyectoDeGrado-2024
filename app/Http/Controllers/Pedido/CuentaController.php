<?php
namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuentaController extends Controller
{
    public function store(Request $request, $idCuenta)
    {
        // Validar los datos
        $validarDatos = Validator::make($request->all(), [
            'razon_social' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:20',
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }

      
        $cuenta = Cuenta::find($idCuenta);
        if (!$cuenta) {
            return response()->json(['status' => 'error', 'error' => 'No se encontró una cuenta con el ID proporcionado.'], 404);
        }

        // Guardar razón social y NIT en la cuenta
        $cuenta->nombre_razon_social = $request->razon_social;
        $cuenta->nit = $request->nit;
        $cuenta->save();

        return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
    }
}

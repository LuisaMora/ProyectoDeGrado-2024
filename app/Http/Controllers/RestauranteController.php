<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    public function show()
    {
        $usuario = auth()->user();
        $tipoUsuario = auth()->user()->tipo_usuario;
        if ($tipoUsuario == 'Propietario') {
            $idRestaurante = Propietario::select('id_restaurante')
            ->where('id_usuario', $usuario->id)
            ->first()->id_restaurante;
        } elseif ($tipoUsuario == 'Empleado') {
            $idRestaurante = Empleado::select('id_restaurante')
            ->where('id_usuario', $usuario->id)
            ->first()->id_restaurante;
        } else {
            return response()->json(['message' => 'No puedes acceder a esta ruta.'], 403);
        }
        $restaurante = Restaurante::find($idRestaurante);
        if ($restaurante == null) {
            return response()->json(['message' => 'Restaurante no encontrado.'], 404);
        }
        return response()->json(['restaurante' => $restaurante], 200);
    }
}

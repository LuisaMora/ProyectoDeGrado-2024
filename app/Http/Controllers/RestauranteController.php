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
        $usuario = User::find(auth()->user()->id);
        $tipoUsuario = $usuario->getTipoUsuario();
        if($tipoUsuario == 'Propietario'){
            $idRestaurante = Propietario::where('id_usuario', $usuario->id)->first()->restaurante->id;
        }elseif ($tipoUsuario == 'Empleado') {
            $empleado = Empleado::where('id_usuario', $usuario->id)->first();
            $idRestaurante = Propietario::find($empleado->id_propietario)->restaurante->id;
        }else{
            return response()->json(['message' => 'No puedes acceder a esta ruta.'], 403);
        }
        $restaurante = Restaurante::find($idRestaurante);
        if ($restaurante == null) {
            return response()->json(['message' => 'Restaurante no encontrado.'], 404);
        }
        return response()->json(['restaurante' => $restaurante], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;

class MesaController extends Controller
{
    public function index()
    {
        $id_usuario = auth()->user()->id;
        $usuario = User::find($id_usuario);
        $tipo_usuario = $usuario->getTipoUsuario();

        if($tipo_usuario == 'Propietario'){
            $mesas = Propietario::where('id_usuario', $id_usuario)->first()->restaurante->mesa;
        }elseif($tipo_usuario == 'Empleado') {
            $mesas = Empleado::where('id_usuario', $id_usuario)->first()->propietario->restaurante->mesa;
        }else{
            return response()->json(['status' => 'error', 'message' => 'Este usuario no puede ver las mesas'], 403);
        }
        return response()->json(['status' => 'success', 'mesas' => $mesas], 200);
        

    }
}
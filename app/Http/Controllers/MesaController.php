<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Mesa;
use App\Models\Propietario;

class MesaController extends Controller
{
    public function index($idRestaurante)
    {
        $id_usuario = auth()->user()->id;
        $tipo_usuario = auth()->user()->tipo_usuario;

        if($tipo_usuario == 'Propietario' || $tipo_usuario == 'Empleado'){
            $mesas = Mesa::select('id','id_restaurante','nombre')->where('id_restaurante', $idRestaurante)->get();
        }else{
            return response()->json(['status' => 'error', 'message' => 'Este usuario no puede ver las mesas'], 403);
        }
        return response()->json(['status' => 'success', 'mesas' => $mesas], 200);
        

    }
}
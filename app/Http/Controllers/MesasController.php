<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Propietario;

class MesasController extends Controller
{
    public function index()
    {
        $id_usuario = auth()->user()->id;
        $propietario = Propietario::where('id_usuario', $id_usuario)->first();
        if ($propietario) {
            $mesas = $propietario->restaurante->mesa;
        
            return response()->json(['status' => 'success', 'mesas' => $mesas], 200);
        }else{
            return response()->json(['status' => 'error', 'message' => 'No se encontro al propietario'], 404);
        }

    }
}
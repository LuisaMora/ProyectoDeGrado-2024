<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    public function show()
    {
        $restaurante = Propietario::find(auth()->user()->id)->restaurante;
        if ($restaurante == null) {
            return response()->json(['message' => 'Restaurante no encontrado.'], 404);
        }
        return response()->json(['restaurante' => $restaurante], 200);
    }
}

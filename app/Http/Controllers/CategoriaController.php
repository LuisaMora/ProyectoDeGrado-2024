<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    function index() {
        $categorias = Categoria::all();
        return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
    }

    function update(Request $request, $id) {
        return response()->json(['status' => 'success', 'categorias' => $request->all()], 200);
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->nombre = $request->nombre;
            $categoria->save();
            return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Categoria no encontrada'], 404);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    function index() {
        $categorias = Categoria::all();
        return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
    }

    function store(Request $request) {
        $request->validate([
            'nombre' => 'required|max:100',
            'imagen' => 'required|image',
        ]);

        $imagen = $request->file('imagen');
        $categoriaImg = md5_file($imagen->getRealPath()) .'.'. $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/categoria', $categoriaImg);

        $categoria = Categoria::create($request->all());
        $categoria->imagen=Storage::url($path);
        $categoria->save();
        
    
        return response()->json(['status' => 'success', 'categoria' => $categoria], 201);
    }

    function show($id) {
        $categoria = Categoria::find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }
    
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }

    function update(Request $request, $id) {
        $categoria = Categoria::find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }

        $request->validate([
            'nombre' => 'required|max:100',
            'imagen' => 'required|image',
            'descripcion' => 'required|max:255',
        ]);

        $categoria->update($request->all());
        $imagen=$request->file('imagen');
        if ($imagen != null) {
            $categoriaImg = md5_file($imagen->getRealPath()) .'.'. $imagen->getClientOriginalExtension();
            $path = $imagen->storeAs('public/categorias', $categoriaImg);
            $categoria->imagen = Storage::url($path);
        }
        $categoria->save(); 
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }

    function destroy($id) {
        $categoria = Categoria::find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }

        $categoria->delete();
        return response()->json(['status' => 'success', 'message' => 'Categoria eliminada.'], 200);
    }
}

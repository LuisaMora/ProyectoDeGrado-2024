<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Platillo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    function index() {
        $categorias = Categoria::where('estado', true)->get();
        return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
    }

    function store(Request $request) {
        $validarDatos = Validator::make($request->all(), [
            'nombre' => 'required|max:100',
            'imagen' => 'required|image',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }

        $imagen = $request->file('imagen');
        $categoriaImg = md5_file($imagen->getRealPath()).time() .'.'. $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/categoria', $categoriaImg);

        $categoria = Categoria::create($request->all());
        $categoria->imagen=Storage::url($path);
        $categoria->save();
        
    
        return response()->json(['status' => 'success', 'categoria' => $categoria], 201);
    }

    function show($id) {
        $categoria = Categoria::where('estado', true)->find($id);
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

        $categoria->update($request->all());
        $imagen=$request->file('imagen');
        if ($imagen != null) {
            $categoriaImg = md5_file($imagen->getRealPath()).time() .'.'. $imagen->getClientOriginalExtension();
            $path = $imagen->storeAs('public/categorias', $categoriaImg);
            $categoria->imagen = Storage::url($path);
        }
        $categoria->save(); 
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }

    function destroy($id) {
        if ((int)$id === 1) {
            return response()->json(['message' => 'No se puede eliminar la categoria por defecto.'], 400);
        }
        $categoria = Categoria::find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }
        $categoria->estado = false;
        $categoria->save();
        Platillo::where('id_categoria', $id)
        ->update(['id_categoria' => 1]);
        return response()->json(['status' => 'success', 'message' => 'Categoria eliminada.'], 200);
    }
}

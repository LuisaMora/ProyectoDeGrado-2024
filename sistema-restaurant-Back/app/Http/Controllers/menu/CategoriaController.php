<?php

namespace App\Http\Controllers\menu;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    public function index()
    {
        $categoria = Categoria::all();
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }
    public function store(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'nombre' => 'required|max:100',
            'imagen' => 'required|image',
        ]);
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }
        $imagen = $request->file('imagen');
        $categoriaImg = md5_file($imagen->getRealPath()) .'.'. $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/categorias', $categoriaImg);
        $categoria =Categoria::create($request->all());
        $categoria->imagen = Storage::url($path);
        $categoria->save();
        return response()->json(['status' => 'success'], 200);
    }
    public function show($id)
    {
        return response()->json(['status' => 'success'], 200);
    }
    public function update(Request $request, $id)
    {
        return response()->json(['status' => 'success'], 200);
    }
    public function destroy($id)
    {
        return response()->json(['status' => 'success'], 200);
    }
}

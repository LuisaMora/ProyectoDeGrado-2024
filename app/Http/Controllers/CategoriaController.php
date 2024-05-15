<?php

namespace App\Http\Controllers;

use App\Utils\ImageHandler;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Platillo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    function index()
    {
        $categorias = Categoria::where('estado', true)->get();
        return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
    }

    function store(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'nombre' => 'required|max:100|min:2',
            'imagen' => 'required|image',
        ]);
        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }

        $imagen = ImageHandler::guardarImagen($request->file('imagen'), 'categorias');

        $categoria = Categoria::create($request->all());
        $categoria->imagen = $imagen;
        $categoria->save();


        return response()->json(['status' => 'success', 'categoria' => $categoria], 201);
    }

    function show($id)
    {
        $categoria = Categoria::where('estado', true)->find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }

        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }

    function update(Request $request, $id)
    {
        DB::beginTransaction();
        $categoria = Categoria::find($id);
        if ($categoria == null) {
            return response()->json(['message' => 'Categoria no encontrada.'], 404);
        }
        //se recupera antes por que al hace rel update se pierde la ruta de la imagen
        $rutaImagen = $categoria->imagen;
        $categoria->update($request->all());
        if ($request->imagen != null) {
            //$categoria->imagen = '/storage/categorias/05bc509bb2added0a71009299593455a1715808837.jpeg'
            $res = ImageHandler::eliminarImagenes([$rutaImagen]);
            if (!$res) {
                DB::rollBack();
                return response()->json(['message' => 'Error al eliminar la imagen.'], 500);
            }
            $imagen = ImageHandler::guardarImagen($request->file('imagen'), 'categorias');
            $categoria->imagen = $imagen;
        }
        $categoria->save();
        DB::commit();
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }

    function destroy($id)
    {
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

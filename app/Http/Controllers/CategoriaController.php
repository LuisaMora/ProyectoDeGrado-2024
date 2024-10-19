<?php

namespace App\Http\Controllers;

use App\Utils\ImageHandler;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Platillo;
use App\Models\Restaurante;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    public function index($id_restaurante)
    {
        // Obtén el id_menu directamente de la tabla restaurante
        $id_menu = Restaurante::where('id', $id_restaurante)->value('id_menu');
        
        // Verifica que se haya encontrado un id_menu
        if (!$id_menu) {
            return response()->json(['status' => 'error', 'message' => 'Menu not found for the given restaurant.'], 404);
        }
        
        // Obtén las categorías asociadas al id_menu y con estado true
        $categorias = Categoria::where('id_menu', $id_menu)->where('estado', true)->get();
        
        // Verifica que se hayan encontrado categorías
        if ($categorias->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No categories found for the given menu.'], 404);
        }
        
        return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
    }
    
    function store(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'id_restaurante'=>'required|min:1',
            'nombre' => 'required|max:100|min:2',
            'imagen' => 'required|image',
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }
        $restaurante=Restaurante::find($request->id_restaurante);
        $imagen = ImageHandler::guardarArchivo($request->file('imagen'), 'categorias');

        $categoria = new Categoria();
        $categoria->nombre=$request->nombre;
        $categoria->imagen = $imagen;
        $categoria->id_menu = $restaurante->id_menu;
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
            $res = ImageHandler::eliminarArchivos([$rutaImagen]);
            // if (!$res) {
                // DB::rollBack();
                // return response()->json(['message' => 'Error al eliminar la imagen.'], 500);

            // }
            $imagen = ImageHandler::guardarArchivo($request->file('imagen'), 'categorias');
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
        return response()->json(['status' => 'success', 'message' => 'Categoria eliminada.','categoria' => $categoria], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Platillo;
use App\Models\Restaurante;
use App\Utils\ImageHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class PlatilloController extends Controller
{
    public function index($id_restaurante)
    {
        // Obtén el menú asociado al restaurante
        $menuId = Restaurante::where('id', $id_restaurante)->value('id_menu');
        // Obtén los platillos disponibles que pertenecen al menú del restaurante
        $platillos = Platillo::with('categoria')
            ->where('id_menu', $menuId)
            ->where('disponible', true)
            ->orderBy('nombre')
            ->get();
    
        return response()->json(['status' => 'success', 'platillos' => $platillos], 200);
    }
    
    public function platillosDisponibles()
    {
        $platillo = Platillo::with('categoria')->where('disponible', true)->where('plato_disponible_menu', true)
        ->orderBy('nombre')->get();
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
    }
    public function store(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'nombre' => 'required|max:100',
            'descripcion' => 'required|max:255',
            'precio' => 'required|numeric',
            'imagen' => 'required|image',
            'id_categoria' => 'required|numeric',
            'id_restaurante' => 'required|numeric',
        ]);
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }
        $id_restaurante = $request->input('id_restaurante');
        
        $path = ImageHandler::guardarArchivo($request->file('imagen'),'platillos');
        $restaurante = Restaurante::find($id_restaurante);
        $request->merge(['id_menu' => $restaurante->id_menu]);
        $platillo = Platillo::create($request->all());
        $platillo->imagen = $path;
        $platillo->save();
        return response()->json(['status' => 'success', 'platillo' => $platillo, 'message' => 'Plato registrado exitosamente !'], 201);
    }
    public function show($id)
    {
        $platillo = Platillo::with('categoria')->where('disponible', true)->find($id);
        if ($platillo == null) {
            return response()->json(['message' => 'Platillo no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
    }
    public function update(Request $request, $id)
    {
        $platillo = Platillo::find($id);
        if ($platillo == null) {
            return response()->json(['message' => 'Platillo no encontrado.'], 404);
        }
        $platillo->update($request->all());
        $imagen = $request->file('imagen');
        if ($imagen != null) {
            $platilloImg = md5_file($imagen->getRealPath()) .'.'. $imagen->getClientOriginalExtension();
            $path = $imagen->storeAs('public/platillos', $platilloImg);
            $platillo->imagen = Storage::url($path);
        }
        $platillo->save();
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
    }
    public function destroy($id)
    {
        $platillo = Platillo::find($id);
        if ($platillo == null) {
            return response()->json(['message' => 'Platillo no encontrado.'], 404);
        }
        $platillo->delete();
        //borrar imagen del storage
        $imagen = $platillo->imagen;
        $imagen = str_replace('storage', 'public', $imagen);
        Storage::delete($imagen);
        return response()->json(['status' => 'success'], 200);
    }
}

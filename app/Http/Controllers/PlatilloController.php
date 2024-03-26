<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Platillo;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PlatilloController extends Controller
{
    public function index()
    {

        $platillo = Platillo::with('categoria')->get();
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
        ]);
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }
        $id_restaurante = $request->input('id_restaurante');
        $id_categoria = $request->input('id_categoria');
        if ($id_restaurante == null || $id_categoria == null) {
            return response()->json(['message' => 'Datos incompletos.'], 422);
        }

        $imagen = $request->file('imagen');
        $platilloImg = md5_file($imagen->getRealPath()) .'.'. $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/platillos', $platilloImg);

        $request->merge(['id_menu' => Restaurante::find($id_restaurante)->first()->id_menu]);
        $platillo = Platillo::create($request->all());
        $platillo->imagen = Storage::url($path);
        $platillo->save();
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
    }
    public function show($id)
    {
        $platillo = Platillo::with('categoria')->find($id);
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
        return response()->json(['platillo' => $platillo, 'id' => $id], 200);

        return response()->json(['status' => 'success'], 200);
    }
    public function destroy($id)
    {
        return response()->json(['status' => 'success'], 200);
    }
}

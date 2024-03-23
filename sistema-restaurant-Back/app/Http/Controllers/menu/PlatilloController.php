<?php

namespace App\Http\Controllers\menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
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
        return response()->json(['auth' => auth()], 200);

        $platillo = Platillo::all();
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
        DB::beginTransaction();
        $id_restaurante= $request->input('id_restaurante');
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
        DB::commit();
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
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

<?php

namespace App\Http\Controllers\menu;

use App\Http\Controllers\Controller;
use App\Models\Platillo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlatilloController extends Controller
{
    
    public function index()
    {
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
            'categoria_id' => 'required|numeric',
        ]);
        DB::beginTransaction();
        $platillo = Platillo::create($request->all());
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

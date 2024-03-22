<?php

namespace App\Http\Controllers\menu;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categoria = Categoria::all();
        return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
    }
    public function store(Request $request)
    {
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

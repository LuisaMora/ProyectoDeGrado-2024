<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Platillo;
use App\Models\Propietario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $id_usuario = auth()->user()->id;
        $propietario = Propietario::where('id_usuario', $id_usuario)->first();

        if ($propietario) {
            $menu = $propietario->restaurante->menu;
            $platillos = Platillo::with('categoria')->where('id_menu', $menu->id)->where('disponible',true)->get();
            return response()->json(['status' => 'success', 'menu' => $menu, 'platillos' => $platillos], 200);
        }
        return view('menu.index');
    }
    public function storeMenu(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'id_menu' => 'required|numeric',
            'portada' => 'required|image',
            'tema' => 'required|max:100',
            'platillos' => 'required|array|min:1',
        ]);
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }

        $menu = Menu::find($request->id_menu);
        if ($menu == null) {
            return response()->json(['message' => 'Menu no encontrado.'], 404);
        }
        $imagen = $request->file('portada');
        $platilloImg = md5_file($imagen->getRealPath()) . '.' . $imagen->getClientOriginalExtension();
        $path = $imagen->storeAs('public/portadas', $platilloImg);

        $menu->imagen = Storage::url($path);
        $menu->tema = $request->tema;
        $menu->qr = $request->qr;
        $menu->save();
        return response()->json(['status' => 'success', 'menu' => $menu], 200);
    }
}

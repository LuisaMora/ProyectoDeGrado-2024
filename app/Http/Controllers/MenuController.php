<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Platillo;
use App\Models\Propietario;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

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
            'tema' => 'required|max:100',
            'platillos' => 'required|string',
        ]);
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }
        $platillos = json_decode($request->platillos, true);
        $menu = Menu::find($request->id_menu);
        if ($menu == null) {
            return response()->json(['message' => 'Menu no encontrado.'], 404);
        }
        
        $imagen = $request->file('portada');
        if($imagen != null){
            $platilloImg = md5_file($imagen->getRealPath()) . '.' . $imagen->getClientOriginalExtension();
            $path = $imagen->storeAs('public/portadas', $platilloImg);
    
            $menu->portada = Storage::url($path);
        }
        $menu->tema = $request->tema;
        $menu->qr = $request->qr;
        $menu->save();
        foreach ($platillos as $platilloMenu) {
            $platillo = Platillo::find($platilloMenu['id']);
            $platillo->plato_disponible_menu = $platilloMenu['plato_disponible_menu'];
            $platillo->update();
        }
        return response()->json(['status' => 'success', 'menu' => $menu], 200);
    }

    function generateQr(Request $request){
        $validate = Validator::make($request->all(), [
            'direccion_url_menu' => 'required|url',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 'error', 'error' => $validate->errors()], 400);
        }
        $id_usuario = auth()->user()->id;
        $propietario = Propietario::where('id_usuario', $id_usuario)->first();
        if ($propietario) {
            $menu = $propietario->restaurante->menu;
            $tiempo = time().'_'.$menu->id;
            $path = storage_path('app\public\codigos_qr\qr_'.$tiempo . '.png');
            $writer = new PngWriter();
            $qrCode = QrCode::create('Life is too short to be generating QR codes')
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
            $label = Label::create('Label')
            ->setTextColor(new Color(255, 0, 0));
            $result = $writer->write($qrCode,null,$label);
            $result->saveToFile($path);
            $url_codigo_qr = '/storage/codigos_qr/qr_'.$tiempo. '.png';
            $menu->qr = $url_codigo_qr;
            $menu->save();
            return response()->json(['status' => 'success', 'qr' => $url_codigo_qr], 200);

        }else{
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

    }

    function qr(){
        $id_usuario = auth()->user()->id;
        $propietario = Propietario::where('id_usuario', $id_usuario)->first();
        if ($propietario) {
            $menu = $propietario->restaurante->menu;
            if ($menu->url_menu == '') {
                return response()->json(['message' => 'No se ha generado el QR'], 404);
            }
            return response()->json(['status' => 'success', 'qr' => '$qr'], 200);
        }else{
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
    }
}

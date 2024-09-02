<?php

namespace App\Http\Controllers;

use App\Utils\ImageHandler;
use App\Models\FormularioPreRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PreRegistroController extends Controller
{
    public function store(Request $request)
    {
        // return response()->json(['status' => 'success', 'data' => $request->all()], 200);
        $validatedData = Validator::make($request->all(), [
            'nombre_restaurante' => 'required|string|max:100',
            'nit' => 'required|numeric',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'celular_restaurante' => 'required|string|max:20',
            'correo_restaurante' => 'required|email|max:100',
            'licencia_funcionamiento' => 'required',
            'tipo_establecimiento' => 'required|string|max:100',
            'nombre_propietario' => 'required|string|max:100',
            'apellido_paterno_propietario' => 'required|string|max:100',
            'apellido_materno_propietario' => 'required|string|max:100',
            'cedula_identidad_propietario' => 'required|numeric',
            'correo_propietario' => 'required|email|max:100',
            'fotografia_propietario' => 'required|image',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validatedData->errors()
            ], 422);
        }

        $imagen = $request->file('fotografia_propietario');
        $nombreCarpeta = 'fotografias_propietarios';
        $urlImagen = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);

        $imagen = $request->file('licencia_funcionamiento');
        $nombreCarpeta = 'licencias_funcionamiento';
        $urlImagen = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);

        $formPreRegistro = new FormularioPreRegistro($request->all());
        $formPreRegistro->fotografia_propietario = $urlImagen;
        $formPreRegistro->save();

        return response()->json(['status' => 'success', 'data' => $formPreRegistro], 201);
    }
}

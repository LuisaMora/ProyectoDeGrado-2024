<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarRegistroRestauranteRequest;
use App\Http\Requests\StorePreRegistroRequest;
use App\Models\FormularioPreRegistro;
use App\Services\FormularioRegistroService;
use Illuminate\Database\QueryException;


class PreRegistroController extends Controller
{
    public function __construct(private FormularioRegistroService $formularioRegistroService)
    {
    }

    public function index()
    {
       //ordenadoi por fecha de actualizacion
        $formPreRegistros = FormularioPreRegistro::orderBy('updated_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $formPreRegistros], 200);
    }

    public function store(StorePreRegistroRequest $request)
    {
        try {
            $formPreRegistro = $this->formularioRegistroService->recibirFormulario($request->all());

            return response()->json(['status' => 'success', 'data' => $formPreRegistro], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function confirmar(ConfirmarRegistroRestauranteRequest $request)
    {
        $preRegistroId = $request->query('pre_registro_id');
        $estado = $request->query('estado') ;
        try {
            $this->formularioRegistroService->validarFormulario($preRegistroId, $estado);
            return response()->json(['status' => 'success', 'message' => 'Formulario confirmado'], 200);
        } catch (QueryException $e) {
            // // Manejar violación de clave única
            // if ($e->getCode() == 23505) {
            //     $formPreRegistro = $this->formularioRegistroService->getFormulario($preRegistroId);
            //     FormularioPreRegistro::where(function ($query) use ($formPreRegistro) {
            //         $query->where('nit', $formPreRegistro->nit)->orWhere('correo_propietario', $formPreRegistro->correo_propietario)->orWhere('cedula_identidad_propietario', $formPreRegistro->cedula_identidad_propietario);
            //     })->where('estado', '!=', 'aceptado')->update(['estado' => 'rechazado']);
            //     // ->update(['estado' => 'rechazado']);

            //     return response()->json(['status' => 'error', 'message' => $e], 400);
            // }

            return response()->json(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } 
        
    }
}

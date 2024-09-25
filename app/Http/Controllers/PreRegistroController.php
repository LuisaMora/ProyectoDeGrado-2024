<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreRegistroRequest;
use App\Mail\ConfirmacionPreRegistro;
use App\Mail\RechazoPreRegistro;
use App\Utils\ImageHandler;
use App\Models\FormularioPreRegistro;
use App\Models\Propietario;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

class PreRegistroController extends Controller
{
    public function index()
    {
       //ordenadoi por fecha de actualizacion
        $formPreRegistros = FormularioPreRegistro::orderBy('updated_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $formPreRegistros], 200);
    }

    public function store(StorePreRegistroRequest $request)
    {
        try {
            $formPreRegistro = new FormularioPreRegistro($request->all());

            $imagen = $request->file('fotografia_propietario');
            $nombreCarpeta = 'fotografias_propietarios';
            $urlImagen = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);

            $imagen = $request->file('licencia_funcionamiento');
            $nombreCarpeta = 'licencias_funcionamiento';
            $urlPdf = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);


            $formPreRegistro->fotografia_propietario = $urlImagen;
            $formPreRegistro->licencia_funcionamiento = $urlPdf;
            $formPreRegistro->save();

            return response()->json(['status' => 'success', 'data' => $formPreRegistro], 201);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    public function confirmar(Request $request)
    {
        $preRegistroId = $request->query('pre_registro_id');
        $estado = $request->query('estado');

        try {
            // No puede confirmar dos veces
            DB::beginTransaction();

            // Buscar el formulario de pre-registro
            $formPreRegistro = FormularioPreRegistro::find($preRegistroId);
            if (!$formPreRegistro || $formPreRegistro->estado != 'pendiente') {
                return response()->json(['status' => 'error', 'message' => 'El formulario ya fue confirmado o no existe'], 400);
            }

            // Actualizar estado del formulario
            $formPreRegistro->estado = $estado;
            $formPreRegistro->save();

            if ($estado == 'rechazado') {
                $mensaje  = $request->query('motivo_rechazo');
                Mail::to($formPreRegistro->correo_propietario)->send(new RechazoPreRegistro($formPreRegistro, $mensaje));
                sleep(5);
                Mail::to($formPreRegistro->correo_restaurante)->send(new RechazoPreRegistro($formPreRegistro, $mensaje));
                DB::commit();
                return response()->json(['status' => 'success', 'data' => $formPreRegistro], 200);
            }

            // Crear usuario
            $usuario = new User();
            $usuario->correo = $formPreRegistro->correo_propietario;
            $usuario->password = bcrypt('12345678');
            $usuario->nombre = $formPreRegistro->nombre_propietario;
            $usuario->apellido_paterno = $formPreRegistro->apellido_paterno_propietario;
            $usuario->apellido_materno = $formPreRegistro->apellido_materno_propietario;
            // Generar nickname
            $usuario->nickname = str_replace(' ', '', $formPreRegistro->nombre_restaurante) . $formPreRegistro->nit;
            $usuario->foto_perfil = $formPreRegistro->fotografia_propietario;
            $usuario->save();
            // Crear restaurante
            $restaurante = new Restaurante();
            $restaurante->nombre = str_replace(' ', '', $formPreRegistro->nombre_restaurante);
            $restaurante->nit = $formPreRegistro->nit;
            $restaurante->celular = $formPreRegistro->celular_restaurante;
            $restaurante->correo = $formPreRegistro->correo_restaurante;
            $restaurante->latitud = $formPreRegistro->latitud;
            $restaurante->longitud = $formPreRegistro->longitud;
            $restaurante->licencia_funcionamiento = $formPreRegistro->licencia_funcionamiento;
            $restaurante->tipo_establecimiento = $formPreRegistro->tipo_establecimiento;
            $restaurante->save();

            // Asignar restaurante al propietario
            $propietario = new Propietario();
            $propietario->id_usuario = $usuario->id;
            $propietario->id_restaurante = $restaurante->id;
            $propietario->id_administrador = auth()->user()->id;
            $propietario->ci = $formPreRegistro->cedula_identidad_propietario;
            $propietario->fecha_registro = now();
            $propietario->pais = $formPreRegistro->pais;
            $propietario->departamento = $formPreRegistro->departamento;
            $propietario->save();

            FormularioPreRegistro::where(function ($query) use ($formPreRegistro) {
                $query->where('nit', $formPreRegistro->nit)->orWhere('correo_propietario', $formPreRegistro->correo_propietario)->orWhere('cedula_identidad_propietario', $formPreRegistro->cedula_identidad_propietario);
            })->where('estado', '!=', 'aceptado')->update(['estado' => 'rechazado']);
            
            //enviar correo de confirmacion con credenciales de acceso
            Mail::to($usuario->correo)->send(new ConfirmacionPreRegistro($usuario, $restaurante));
            //esperar a que se envie el correo
            sleep(5);
            Mail::to($restaurante->correo)->send(new ConfirmacionPreRegistro($usuario, $restaurante));
            DB::commit();

            return response()->json(['status' => 'success', 'data' => $formPreRegistro], 200);
        } catch (QueryException $e) {
            DB::rollBack();

            // Manejar violación de clave única
            if ($e->getCode() == 23505) {
                FormularioPreRegistro::where(function ($query) use ($formPreRegistro) {
                    $query->where('nit', $formPreRegistro->nit)->orWhere('correo_propietario', $formPreRegistro->correo_propietario)->orWhere('cedula_identidad_propietario', $formPreRegistro->cedula_identidad_propietario);
                })->where('estado', '!=', 'aceptado')->update(['estado' => 'rechazado']);
                // ->update(['estado' => 'rechazado']);

                return response()->json(['status' => 'error', 'message' => $e], 400);
            }

            return response()->json(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

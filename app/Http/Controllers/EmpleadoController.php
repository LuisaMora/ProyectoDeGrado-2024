<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Create new User
            $usuario = new User();
            $usuario->nombre = $request->input('nombre');
            $usuario->apellido_paterno = $request->input('apellido_paterno');
            $usuario->apellido_materno = $request->input('apellido_materno');
            $usuario->correo = $request->input('correo');
            $usuario->nickname = $request->input('nickname');
            $usuario->password = Hash::make('12345678'); // Default password
           
            // Verifica si se ha enviado una imagen
            if ($request->hasFile('foto_perfil')) {
             $file = $request->file('foto_perfil');
             $path = $file->store('store/imagenes', 'public'); // Guarda en 'storage/app/public/uploads/imagenes'
             $usuario->foto_perfil = $path; 
            }
            $usuario->save();

            $idPropietario = Propietario::where('id_usuario',auth()->user()->id)->get()[0]->id;

            // Create new Empleado
            $empleado = new Empleado();
            $empleado->id_usuario = $usuario->id;
            $empleado->id_rol = $request->input('id_rol'); // 1: Waiter, 2: Cashier, 3: Cook

            $empleado->id_propietario = $idPropietario;
            $empleado->fecha_nacimiento = $request->input('fecha_nacimiento');
            $empleado->fecha_contratacion = $request->input('fecha_contratacion');
            $empleado->ci = $request->input('ci');
            $empleado->direccion = $request->input('direccion');
            $empleado->save();

            DB::commit();
            return response()->json(['status' => 'success', 'data' => $empleado], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}

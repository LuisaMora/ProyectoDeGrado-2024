<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmpleadoController extends Controller
{public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|numeric|min:7',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|max:255|min:4', 
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validatedData->errors(),
            ], 422);
        }
    
        // Crear un nuevo usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'correo' => $request->correo,
            'nickname' => $request->nombre,
            'foto_de_perfil' => $request->foto_de_perfil, 
            'password' => Hash::make('12345678'),
        ]);
    
        // Obtener el id_propietario a partir del id_usuario
        $idPropietario = auth()->user()->id;
    
        // Guardar datos del empleado
        $empleado = new Empleado();
        $empleado->id_usuario = $user->id; // Relacionar el empleado con el usuario recién creado
        $empleado->id_rol = $request->id_rol; // 1: mesero, 2: cajero, 3: cocinero
        $empleado->id_propietario = $idPropietario; // Relacionar con el propietario
        $empleado->fecha_nacimiento = $request->fecha_nacimiento;
        $empleado->fecha_contratacion = $request->fecha_contratacion;
        $empleado->ci = $request->ci; // Cédula de identidad
        $empleado->direccion = $request->direccion;
        $empleado->save();
    
        // Retornar una respuesta
        return response()->json([
            'message' => 'Empleado y usuario creados exitosamente',
            'empleado' => $empleado,
            'user' => $user
        ], 201);
    }
}

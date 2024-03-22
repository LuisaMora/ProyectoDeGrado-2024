<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function register(Request $request)
    {
        return response()->json(['status' => 'success'], 200);
    }
    public function login(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'usuario' => 'required|max:100',
            'password' => 'required',
        ]);

        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }

        $user = Usuario::where('correo', $request->input('usuario'))
        ->orWhere('nickname', $request->input('usuario'))
        ->first();

        if ($user == null || !Auth::attempt(['correo' => $user->correo,
         'password' => $request->input('password')])) {
            return response()->json([
                'message' => 'Credenciales invalidas'
            ], 401);
        }

        $datosPersonales = $this->getDatosPersonales($user);

        if(!$datosPersonales) return response()->json(['message' => 'No se encontro el tipo de usuario'], 404);
       
        $datosPersonales-> usuario = $user;
        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;
        return response()->json([
            'data' => $datosPersonales,
            'token_acceso' => $token,
            'token_tipo' => 'Bearer',
        ]);
    }

    private function getDatosPersonales($user)
    {
        $nameoftype = Usuario::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM administradores WHERE id_usuario = usuarios.id) THEN 'Administrador'
            WHEN EXISTS (SELECT * FROM propietarios WHERE id_usuario = usuarios.id) THEN 'Propietario'
            WHEN EXISTS (SELECT * FROM empleados WHERE id_usuario = usuarios.id) THEN 'Empleado'
            ELSE ''
        END AS type_user")
            ->where('id', $user->id)
            ->value('type_user');
            
        switch ($nameoftype) {
            case 'Administrador':
                $user_data = Administrador::where('id_usuario', $user->id)->first();
                break;
            case 'Propietario':
                $user_data = Propietario::where('id_usuario', $user->id)->first();
                break;
            case 'Empleado':
                $user_data = Empleado::where('id_usuario', $user->id)->first();
                break;
            default:
                return null;
                break;
        }
        $user_data->tipo = $nameoftype;
        return $user_data;
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesion cerrada'], 200);
    }

}

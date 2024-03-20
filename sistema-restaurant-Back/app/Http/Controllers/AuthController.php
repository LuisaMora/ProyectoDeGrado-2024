<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        if (!Auth::attempt(['correo' => $request->usuario,'password' => $request->input('password')])
         || !Auth::attempt(['nickname' => $request->usuario,'password' => $request->input('password')])) {
            return response()->json([
                'message' => 'Credenciales invalidas'
            ], 401);
        }

        $user = Usuario::where('correo', $request->input('usuario'))
            ->orWhere('nickname', $request->input('usuario'))
            ->first();
        $nameofclass = $this->determinarTipoUsuario($user);

        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'type_user' => $nameofclass
        ]);
    }

    private function determinarTipoUsuario($user)
    {
        return Usuario::selectRaw("
        CASE 
            WHEN EXISTS (SELECT * FROM administradores WHERE id_usuario = usuarios.id) THEN 'Administrador'
            WHEN EXISTS (SELECT * FROM propietarios WHERE id_usuario = usuarios.id) THEN 'Propietario'
            WHEN EXISTS (SELECT * FROM empleados WHERE id_usuario = usuarios.id) THEN 'Empleado'
            ELSE ''
        END AS type_user")
            ->where('id', $user->id)
            ->value('type_user');
    }
}

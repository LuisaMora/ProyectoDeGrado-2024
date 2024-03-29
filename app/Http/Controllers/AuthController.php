<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
        //$user es de tipo User y no mixed
        $user = User::where('correo', $request->input('usuario'))->orWhere(
            'nickname', $request->input('usuario')
        )->first();
        if ($user != null && Auth::attempt([
            'correo' => $user->correo,
            'password' => $request->input('password')
        ])) {
            if ($user instanceof User) {
                // el token expira en 12 horas
                    $token = $user->createToken('personal-token',expiresAt:now()->addHours(12))->plainTextToken;
                    $datosPersonales = $this->getDatosPersonales($user);
                    $datosPersonales->usuario = $user;
            }
            return response()->json([
                'token' => $token,
                'user' => $datosPersonales,
                'message' => 'Inicio de sesión exitoso.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Credenciales invalidas'
            ], 401);
        }
    }

    private function getDatosPersonales(User $user)
    {
        $nameoftype = $user->getTipoUsuario();
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
        $user = Auth::user();
        // Revisa si el usuario está autenticado
        if ($user) {
            // Revoca todos los tokens del usuario
            $user->tokens->each(function ($token, $key) {
                $token->delete();
            });
        }
        return response()->json(['success' => 'Sesion finalizada exitosamente.'], 200);
    }
}

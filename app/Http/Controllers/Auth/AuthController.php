<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mockery\Undefined;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        //$user es de tipo User y no mixed
        $user = $this->attemptLogin($request->usuario, $request->password);
        if ($user) {
            // el token expira en 12 horas - probar
            $token = $user->createToken('personal-token', expiresAt: now()->addHours(12))->plainTextToken;
            $datosPersonales = $this->getDatosPersonales($user);
            $datosPersonales->usuario = $user;
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

    private function attemptLogin($userCredential, $password): User | null
    {
        $user = User::where('correo', $userCredential)->orWhere(
            'nickname', $userCredential)->first();
        if ($user != null && Auth::attempt(['correo' => $user->correo, 'password' => $password])) {
            return $user;
        }
        return null;
    }

    public function logout()
    {
        $user = auth()->user();
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

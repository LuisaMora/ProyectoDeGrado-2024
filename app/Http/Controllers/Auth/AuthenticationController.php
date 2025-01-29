<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CambioContraseniaRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetContraseniaRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthenticationController extends Controller
{
    // Maneja las operaciones relacionadas con el inicio/cierre de sesión y recuperación de contraseñas.
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $credentials)
    {
        try {
            $data = $this->authService->login($credentials['usuario'], $credentials['password']);
            return response()->json(['token' =>  $data['token'],'user' => $data['datosPersonales'],
                'message' => 'Inicio de sesión exitoso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function register(Request $request)
    {
        // $data = $request->all();
        // $user = $this->authService->register($data);

        // return response()->json(['user' => $user], 201);
    }

    public function solicitarCambioContrasenia(CambioContraseniaRequest $request)
    {
       try {
            $this->authService->forgotPassword($request['correo'] , $request['direccion_frontend']);
            return response()->json(['message' => 'Correo de restablecimiento enviado.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function restablecerContrasenia(ResetContraseniaRequest $request)
    {
        try {
            $this->authService->resetPassword($request['token'],$request['oldPassword'], $request['newPassword']);
            return response()->json(['message' => 'Contraseña actualizada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function logout()
    {
        $this->authService->logout();

        return response()->json(['success' => 'Sesion finalizada exitosamente.']);
    }

}
<?php

namespace App\Services;

use App\Repositories\AdministradorRepository;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private PropietarioRepository $propietarioRepository,
        private EmpleadoRepository $empleadoRepository,
        private AdministradorRepository $administradorRepository,
        private EmailService $emailService
    ) {
        
    }

    public function register(array $data)
    {
        // Implement registration logic here
    }

    public function login(string $usuario, string $password)
    {
        $usuario = $this->usuarioRepository->findBy($usuario);
        if ($usuario && Hash::check($password, optional($usuario)->password)) {
            $token = $usuario->createToken('personal-token', expiresAt: now()->addHours(12))->plainTextToken;
            $datosPersonales = $this->getDatosPersonales($usuario->id, $usuario->tipo_usuario);
            $datosPersonales->usuario = $usuario;
            return ['datosPersonales' => $datosPersonales, 'token' => $token];
        } else {
            throw new \Exception('Usuario o contraseña inválidos.', 401);
        }
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
    }

    public function refreshToken(string $token)
    {
        // Implement token refresh logic here
    }

    public function forgotPassword(string $correo, string $direccionFrontend)
    {
        $token = Str::random(60);
        // Implement password reset logic here
        $usuario = $this->usuarioRepository->findBy($correo);
        if ($usuario) {
            // Enviar correo con el token de recuperación
            $email = new \App\Mail\ResetPasswordMail($token, $direccionFrontend);
            $data = ['reset_token' => $token, 'reset_token_expires_at' => now()->addMinutes(60)];
            $usuario = $this->usuarioRepository->update($usuario->id, $data);
            $this->emailService->sendEmail($correo, $email);
        }
        throw new \Exception('Usuario no encontrado', 404);
    }

    public function resetPassword(string $token, string $password)
    {
        // Implement password reset logic here
    }

    private function getDatosPersonales($userId, $tipoUsuario)
    {
        switch ($tipoUsuario) {
            case 'Propietario':
                $user_data = $this->propietarioRepository->findByUserId($userId);
                break;
            case 'Empleado':
                $user_data = $this->empleadoRepository->findByUserId($userId);
                break;
            case 'Administrador':
                $user_data = $this->administradorRepository->findByUserId($userId);
                break;
                // Agregar otros casos según sea necesario
        }
        return $user_data;
    }
}

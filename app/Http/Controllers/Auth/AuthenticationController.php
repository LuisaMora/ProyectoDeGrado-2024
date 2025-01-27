<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = $this->authService->login($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token]);
    }

    public function register(Request $request)
    {
        $data = $request->all();
        $user = $this->authService->register($data);

        return response()->json(['user' => $user], 201);
    }

    public function logout(Request $request)
    {
        $this->authService->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(Request $request)
    {
        $user = $this->authService->me();

        return response()->json(['user' => $user]);
    }
}
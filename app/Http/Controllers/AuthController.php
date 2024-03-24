<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('correo', 'password');
        if (Auth::attempt($credentials)) {
            $user = User::where('correo', $request->correo)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'message' => 'Login successful'
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
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
        return response()->json(['success' => 'Session logout'], 200);
    }
}

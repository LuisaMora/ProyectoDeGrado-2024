<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function register(Request $request)  {
        return response()->json(['status' => 'success'], 200);
    }
    public function login(Request $request)
    {
        
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);
        return response()->json([
            'message' => 'A la madre validas'
        ]);
        // Verificar las credenciales
        if (!Auth::attempt($request->only('correo', 'password'))) {
            return response()->json([
                'message' => 'Credenciales invalidas'
            ], 401);
        }
        $user = Usuario::where('correo', $request['correo'])->firstOrFail();
        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}

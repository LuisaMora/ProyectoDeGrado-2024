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
    function register(Request $request)  {
        return response()->json(['status' => 'success'], 200);
    }
    public function login(Request $request)
    {
        
        $validarDatos = Validator::make($request->all(), [
            'correo' => 'required|email',
            'password' => 'required',
        ]);
        if($validarDatos->fails()){
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }else{
            if (!Auth::attempt($request->only('correo', 'password'))) {
                return response()->json([
                    'message' => 'Credenciales invalidas'
                ], 401);
            }
            $user = Usuario::where('correo', $request['correo'])->firstOrFail();
            $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;
            // Verificar si es administrador
            $nameofclass = '';
            $type_user = Administrador::find($user->id)->first() ?? $nameofclass= 'Administrador';
            if (!$type_user) $type_user = Propietario::find($user->id)->first() ?? $nameofclass= 'Propietario';
            if (!$type_user) $type_user = Empleado::find($user->id)->first() ?? $nameofclass= 'Empleado';
            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'type_user' => $nameofclass
            ]);
        }
        // Verificar las credenciales
        
    }
}

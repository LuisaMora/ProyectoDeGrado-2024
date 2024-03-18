<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'password' => 'required|string',
            'correo' => 'required|correo|unique:usuarios',
            'nickname' => 'required|string|max:100',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $image = $request->file('foto_perfil');
        $pngFile = md5_file($image->getRealPath()) .'.'. $image->getClientOriginalExtension();
        $path = $image->storeAs('public/images', $pngFile);

        $user = Usuario::create([
            'nombre' => $request['nombre'],
            'apellido_paterno' => $request['apellido_paterno'],
            'apellido_materno' => $request['apellido_materno'],
            'contrasenia' => Hash::make($request['contrasenia']),
            'correo' => $request['correo'],
            'nickname' => $request['nickname'],
            'foto_perfil' => $path,
        ]);

        // Auth::login($user);
        // $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

        // return response()->json([
        //     'usuario' => $user,
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        // ], 201);
    }

    // public function logout() 
    // {
    //     $user = Auth::user();
    //     // Revisa si el usuario está autenticado
    //     if ($user) {
    //         // Revoca todos los tokens del usuario
    //         $user->tokens->each(function ($token, $key) {
    //             $token->delete();
    //         });
    //     }
    //     return response()->json(['success' => 'Session logout'], 200);
    // }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'correo_or_nickname ' => 'required|max:100',
    //         'contrasenia' => 'required',
    //     ]);

    //     // Verificar las credenciales
    //     if (!Auth::attempt($request->only('correo', 'contrasenia'))) {
    //         return response()->json([
    //             'message' => 'Credenciales inválidas'
    //         ], 401);
    //     }

    //     $user = Usuario::where('correo', $request['correo'])->firstOrFail();
    //     $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

    //     return response()->json([
    //         'user' => $user,
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //     ]);
    // }
}

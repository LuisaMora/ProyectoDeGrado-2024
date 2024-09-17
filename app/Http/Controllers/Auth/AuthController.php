<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Utils\ImageHandler;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'usuario' => 'required|max:100|min:2',
            'password' => 'required|max:100|min:2',
        ]);

        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }
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

    public function show(Request $request)
{
    $id_usuario = $request->query('id_usuario');
    
    if (!$id_usuario) {
        return response()->json([
            'message' => 'El ID del usuario es requerido'
        ], 400); 
    }

    $user = User::find($id_usuario);
    
    if (!$user) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }
    $datosPersonales = $this->getDatosPersonales($user);

    if (!$datosPersonales) {
        return response()->json([
            'message' => 'No se encontraron datos personales para este usuario'
        ], 404);
    }
    $datosPersonales->usuario = $user;
    return response()->json([
        'user' => $datosPersonales
    ], 200);
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
                $user_data->id_restaurante = Propietario::select('id_restaurante')->where('id', $user_data->id_propietario)->first()->id_restaurante;
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
            'nickname',
            $userCredential
        )->first();
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

    public function updateDatosPersonales(Request $request)
    {
        $validarDatos = Validator::make($request->all(), [
            'nombre' => 'required|max:100|min:2',
            'apellido_paterno' => 'required|max:100|min:2',
            'apellido_materno' => 'required|max:100|min:2',
            'correo' => 'required|email|max:150',
            'nickname' => 'required|max:100|min:2',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // La imagen es opcional
            'ci' => 'required|integer|min:1',
            'id_usuario' => 'required|integer|min:1'
        ]);

        // Verificar si la validación falló
        if ($validarDatos->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validarDatos->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::find(auth()->user()->id);
            $user_data = $this->getDatosPersonales($user);
            $user_data->ci = $request->ci;
            //quitar tipo del user_data
            unset($user_data->tipo);
            $user_data->save();

            $user->nombre = $request->nombre;
            $user->apellido_paterno = $request->apellido_paterno;
            $user->apellido_materno = $request->apellido_materno;
            $user->correo = $request->correo;
            $user->nickname = $request->nickname;
            if ($request->hasFile('foto_perfil')) {
                ImageHandler::eliminarArchivos([$user->foto_perfil]);
                $user->foto_perfil = ImageHandler::guardarArchivo($request->foto_perfil, 'fotografias_propietarios');
            }


            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Datos actualizados correctamente',
                'user' => $user,
                'user_data' => $user_data
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar los datos',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function solicitarCambioContrasena(Request $request)
    {
        // Validar el correo electrónico
        $request->validate([
            'correo' => 'required|email|exists:users,email',
            'direccion_frontend' => 'required|url'
        ]);

        $user = User::where('correo', $request->correo)->first();

        if ($user) {
            // Generar un token de restablecimiento
            $token = Str::random(60);
            $user->reset_token = $token;
            $user->reset_token_expires_at = now()->addMinutes(60); // Token válido por 60 minutos
            $user->save();

            // Enviar correo con el enlace de restablecimiento
            Mail::to($user->correo)->send(new \App\Mail\ResetPasswordMail($token, $request->direccion_frontend));

            return response()->json(['message' => 'Correo de restablecimiento enviado.']);
        }

        return response()->json(['message' => 'Error al solicitar el cambio de contraseña.'], 500);
    }

    public function restablecerContrasena(Request $request)
{
    // Validar los datos
    $request->validate([
        'token' => 'required',
        'newPassword' => 'required|min:6|confirmed', // Confirmar que la contraseña es igual en los dos campos
    ]);

    // Buscar al usuario por el token
    $user = User::where('reset_token', $request->token)
                ->where('reset_token_expires_at', '>', now())
                ->first();

    if ($user) {
        // Actualizar la contraseña
        $user->password = Hash::make($request->newPassword);
        $user->reset_token = null; // Eliminar el token después de usarlo
        $user->reset_token_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    return response()->json(['message' => 'Token inválido o expirado.'], 400);
}

}

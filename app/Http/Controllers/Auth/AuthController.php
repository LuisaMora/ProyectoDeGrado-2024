<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Utils\ImageHandler;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $validarDatos = Validator::make($request->all(), [
    //         'usuario' => 'required|max:60|min:6',
    //         'password' => 'required|max:60|min:6',
    //     ]);
    //     if ($validarDatos->fails()) {
    //         return response()->json([
    //             'message' => 'Datos invalidos',
    //             'errors' => $validarDatos->errors()
    //         ], 422);
    //     }

    //     $user = $this->attemptLogin($request->usuario, $request->password);
    //     if ($user) {
    //         $token = $user->createToken('personal-token', expiresAt: now()->addHours(12))->plainTextToken;
    //         $datosPersonales = $this->getDatosPersonales($user);
    //         $datosPersonales->usuario = $user;
    //         return response()->json([
    //             'token' => $token,
    //             'user' => $datosPersonales, // Se asume que este método ya incluye el usuario
    //             'message' => 'Inicio de sesión exitoso.'
    //         ], 200);
    //     } else {
    //         // Implementar un sistema de rate limiting o captchas para múltiples intentos fallidos
    //         return response()->json([
    //             'message' => 'Usuario o contraseña invalidos.'
    //         ], 401);
    //     }
    // }

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
        switch ($user->tipo_usuario) {
            case 'Propietario':
                $user_data = Propietario::select('id', 'id_administrador', 'ci', 'fecha_registro', 'pais', 'departamento', 'id_restaurante')
                    ->where('id_usuario', $user->id)->first();
                break;
            case 'Empleado':
                $user_data = Empleado::select('id', 'ci', 'fecha_nacimiento', 'fecha_contratacion', 'direccion', 'id_rol', 'id_restaurante')
                    ->where('id_usuario', $user->id)->first();
                break;
            case 'Administrador':
                $user_data = Administrador::select('id')->where('id_usuario', $user->id)->first();
                break;
                // Agregar otros casos según sea necesario
        }
        return $user_data;
    }


    // private function attemptLogin($usuario, $password)
    // {
    //     $user = User::where('correo', $usuario)
    //         ->orWhere('nickname', $usuario) // Asumiendo que el campo 'nickname' existe en tu modelo
    //         ->where('estado', true) // Asumiendo que el campo 'estado' existe en tu modelo
    //         ->first();

    //     if ($user && Hash::check($password, $user->password)) {
    //         return $user;
    //     }
    //     return null;
    // }

    // public function logout()
    // {
    //     $user = auth()->user();
    //     // Revisa si el usuario está autenticado
    //     if ($user) {
    //         // Revoca todos los tokens del usuario
    //         $user->tokens->each(function ($token, $key) {
    //             $token->delete();
    //         });
    //     }
    //     return response()->json(['success' => 'Sesion finalizada exitosamente.'], 200);
    // }

    // public function updateDatosPersonales(Request $request)
    // {
    //     $validarDatos = Validator::make($request->all(), [
    //         'nombre' => 'required|max:100|min:2',
    //         'apellido_paterno' => 'required|max:100|min:2',
    //         'apellido_materno' => 'required|max:100|min:2',
    //         'correo' => 'required|email|max:150',
    //         'nickname' => 'required|max:100|min:2',
    //         'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // La imagen es opcional
    //     ]);

    //     // Verificar si la validación falló
    //     if ($validarDatos->fails()) {
    //         return response()->json([
    //             'message' => 'Datos inválidos',
    //             'errors' => $validarDatos->errors()
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $user = $this->actualizarDatosUsuario([''], $request);


    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Datos actualizados correctamente',
    //             'user' => $user,
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error al actualizar los datos',
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    // public function updateDatosEmpleado(Request $request)
    // {
    //     $validarDatos = Validator::make($request->all(), [
    //         'nombre' => 'required|max:100|min:2',
    //         'apellido_paterno' => 'required|max:100|min:2',
    //         'apellido_materno' => 'required|max:100|min:2',
    //         'nickname' => 'required|max:100|min:2',
    //         'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    //     ]);
    //     if ($validarDatos->fails()) {
    //         return response()->json([
    //             'message' => 'Datos inválidos',
    //             'errors' => $validarDatos->errors()
    //         ], 422);
    //     }

    //     $usuario = $this->actualizarDatosUsuario(['correo'], $request);

    //     return response()->json([
    //         'message' => 'Datos actualizados correctamente',
    //         'user' => $usuario
    //     ], 200);
    // }

    // private function actualizarDatosUsuario($excepto, Request $request)
    // {
    //     $user = User::find(auth()->user()->id);
    //     $user->nombre = $request->nombre;
    //     $user->apellido_paterno = $request->apellido_paterno;
    //     $user->apellido_materno = $request->apellido_materno;
    //     if (!in_array('correo', $excepto)) {
    //         $user->correo = $request->correo;
    //     }
    //     $user->nickname = $request->nickname;
    //     if ($request->hasFile('foto_perfil')) {
    //         ImageHandler::eliminarArchivos([$user->foto_perfil]);
    //         $user->foto_perfil = ImageHandler::guardarArchivo($request->foto_perfil, 'fotografias_propietarios');
    //     }
    //     $user->save();

    //     return $user;
    // }

    public function solicitarCambioContrasenia(Request $request)
    {
        // Validar el correo electrónico
        $request->validate([
            'correo' => 'required|email|exists:usuarios,correo',
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
    }

    public function restablecerContrasenia(Request $request)
    {
        // Validar los datos
        $request->validate([
            'token' => 'min:60|max:60',
            'oldPassword' => 'min:6|max:60', // Confirmar que la contraseña es igual en los dos campos
            'newPassword' => 'required|min:6', // Confirmar que la contraseña es igual en los dos campos
        ]);

        if ($request->token) {
            // Buscar al usuario por el token
            $user = User::where('reset_token', $request->token)
                ->where('reset_token_expires_at', '>', now())
                ->first();
        } elseif (auth()->user()) {
            $user = User::find(auth()->user()->id);
            if (!Hash::check($request->oldPassword, $user->password)) {
                return response()->json(['message' => 'La contraseña actual no coincide.'], 400);
            }
        } else {
            return response()->json(['message' => 'Token inválido o expirado.'], 400);
        }



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

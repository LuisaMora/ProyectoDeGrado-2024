<?php

namespace app\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
use App\Models\Empleado;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CuentaUsuarioController extends Controller
{
    public function propietarios()
    {
        $propietarios = Propietario::with('usuario')->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $propietarios], 200);
    }

    public function empleados()
    {
        $idPropietario = Propietario::where('id_usuario', auth()->user()->id)->value('id');
        $empleados = Empleado::with('usuario')
            ->where('id_propietario', $idPropietario)->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $empleados], 200);
    }

    public function cambiarEstadoUsuario($id_usuario, $estado, $rol)
    {
        // Verificar si el usuario existe
        $usuario = User::find($id_usuario);
        if (!$usuario) {
            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
        }

        if ($rol === 'empleado') {
            $relacion = Empleado::where('id_usuario', $id_usuario)->where('id_propietario', auth()->user()->id)->first();
        } else if ($rol === 'propietario') {
            $relacion = Propietario::where('id_usuario', $id_usuario)->first();
        }

        if (!$relacion) {
            return response()->json(['status' => 'error', 'message' => ucfirst($rol) . ' no encontrado'], 404);
        }

        $usuario->estado = $estado;
        $usuario->save();

        // Desactivar todos los tokens del usuario si el estado es activado
        if ($estado) {
            $usuario->tokens()->delete();
            Mail::to($usuario->correo)->send(new AltaUsuario($usuario)); // Correo de activación
        } else {
            Mail::to($usuario->correo)->send(new BajaUsuario($usuario)); // Correo de baja
        }

        // Si el usuario es propietario, actualizar el estado del menú del restaurante
        if ($rol === 'propietario') {
            $menu = $relacion->restaurante->menu;
            $menu->disponible = $estado;
            $menu->save();
        }

        $message = $estado ? ucfirst($rol) . ' activado' : ucfirst($rol) . ' dado de baja';
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }



}

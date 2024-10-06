<?php

namespace app\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
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

    public function cambiarEstadoPropietario($id_usuario, $estado)
    {
        $propietario = Propietario::where('id_usuario', $id_usuario)->first();
        if (!$propietario) {
            return response()->json(['status' => 'error', 'message' => 'Propietario no encontrado'], 404);
        }

        $usuario = User::find($id_usuario);
        if (!$usuario) {
            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
        }

        $usuario->estado = $estado;
        $usuario->save();

        //desactivar todos los tokens del usuario
        if ($estado) {
            
            //despachar correo de activación
            Mail::to($usuario->correo)->send(new AltaUsuario($usuario));
        }else{
            $usuario->tokens()->delete();
            //despachar correo de baja
            Mail::to($usuario->correo)->send(new BajaUsuario($usuario));
        }

        $menu = $propietario->restaurante->menu;
        $menu->disponible = $estado; // Activar o desactivar según el estado
        $menu->save();

        $message = $estado ? 'Propietario activado' : 'Propietario dado de baja';

        return response()->json(['status' => 'success', 'message' => $message], 200);
    }


    // public function store(StoreUserRequest $request)
    // {
    //     try {
    //         $user = new User($request->all());

    //         $user->save();

    //         return response()->json(['status' => 'success', 'data' => $user], 201);
    //     } catch (\Throwable $th) {
    //         return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
    //     }
    // }

}

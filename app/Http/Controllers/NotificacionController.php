<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificacionController extends Controller
{
    public function enviarNotificacion(Request $request)
    {
        // Validar los datos de la solicitud
        $validarDatos = Validator::make($request->all(), [
            'user_id' => 'required|exists:usuarios,id',
            'message' => 'required|string',
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['message' => 'Datos no válidos'], 400);
        }

        // Encuentra al usuario
        $user = User::where('id', $request->user_id)->first();

        // Envía la notificación
        $user->notify(new Notificacion($request->message));

        return response()->json(['message' => 'Notificación enviada con éxito']);
    }
} 
<?php

namespace app\Http\Controllers\Auth ;

use App\Http\Controllers\Controller;
use App\Models\Propietario;
use App\Models\User;

class CuentaUsuarioController extends Controller
{
    public function propietarios()
    {
        $propietarios = Propietario::with('usuario')->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $propietarios], 200);
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
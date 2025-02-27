<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\Request;
use App\Services\UserDetailsService;
use App\Services\UserService;

class UserDetailsController extends Controller
{


    public function __construct(private UserService $userService)
    { }

    public function getUserDetails(Request $request)
    {
        try {
            $userId = $request->query('id_usuario');
            $userDetails = $this->userService->getUserById($userId);
            return response()->json([
                'user' => $userDetails
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function updateUserDetails(UpdateUsuarioRequest $request)
    {
        try {
            if (!$request->esPropietario) {
                $request->offsetUnset('correo');
            }
            $usuario = $this->userService->updateUser($request->all());
            return response()->json(
                    ['status' => 'success', 'data' => $usuario,
                     'message' => 'Datos actualizados correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode()<600 && $e->getCode()>199 ?$e->getCode(): 500);
        }
    }
}
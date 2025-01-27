<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Services\UserService;

class UserManagementController extends Controller
{
    // Se encarga de la gestión y actualización de datos de los usuarios (propietarios y empleados)

    private $userService;

    public function __construct(
        UserService $userService,
    ) {
        $this->userService = $userService;
    }

    public function propietarios()
    {
        try {
            $propietarios = $this->userService->getPerfilUsuarios('propietario');
            return response()->json(['status' => 'success', 'data' => $propietarios], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function empleados()
    {
        try {
            $empleados = $this->userService->getPerfilUsuarios('empleado');
            return response()->json(['status' => 'success', 'data' => $empleados], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function cambiarEstadoUsuario(string $id_usuario, bool $estado, string $rol)
    {
        try {
            $this->userService->cambiarEstadoUsuarioConRol($id_usuario, $estado, $rol);

            $message = $estado ? ucfirst($rol) . ' activado' : ucfirst($rol) . ' dado de baja';
            return response()->json(['status' => 'success', 'message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function updateDatosUsuario(UpdateUsuarioRequest $request)
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }
}

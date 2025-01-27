<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserManagementController extends Controller
{
    // Se encarga de la gestión y actualización de datos de los usuarios (propietarios y empleados)

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function propietarios()
    {
        try {
            $propietarios = $this->userService->getPerfilUsuario('propietario');
            return response()->json(['status' => 'success', 'data' => $propietarios], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function empleados()
    {
        try {
            $empleados = $this->userService->getPerfilUsuario('empleado');
            return response()->json(['status' => 'success', 'data' => $empleados], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function cambiarEstadoUsuario(Request $request, $id)
    {
        // Implementar lógica para cambiar el estado de un usuario
    }

    public function updateDatosPersonales(Request $request, $id)
    {
        // Implementar lógica para actualizar datos personales de un usuario
    }

    public function updateDatosEmpleado(Request $request, $id)
    {
        // Implementar lógica para actualizar datos de un empleado
    }

    public function actualizarDatosUsuario(Request $request, $id)
    {
        // Implementar lógica para actualizar datos de un usuario
    }
}
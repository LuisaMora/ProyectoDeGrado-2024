<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\PropietarioService;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserManagementController extends Controller
{
    // Se encarga de la gestión y actualización de datos de los usuarios (propietarios y empleados)

    private $userService;
    private $menuService;
    private $propietarioService;

    public function __construct(UserService $userService, MenuService $menuService, PropietarioService $propietarioService
    ) {
        $this->userService = $userService;
        $this->menuService = $menuService;
        $this->propietarioService = $propietarioService;
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
            $usuario = $this->userService->cambiarEstadoUsuario($id_usuario, $estado);
            if ($rol === 'propietario') {

                $propietario = $this->propietarioService->getPropietarioByUserId($usuario->id);
                $menu = $this->menuService->getMenuByRestaurantId($propietario->id_restaurante);
                $this->menuService->updateMenu($menu->id, ['disponible' => $estado]);
                $this->userService->cambiarEstadoUsuarioEmpleado($propietario->id, $estado);
            }

            $message = $estado ? ucfirst($rol) . ' activado' : ucfirst($rol) . ' dado de baja';
            return response()->json(['status' => 'success', 'message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
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

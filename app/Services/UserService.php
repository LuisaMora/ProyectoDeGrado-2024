<?php

namespace App\Services;

use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
use App\Mail\RegistroEmpleado;
use App\Models\Empleado;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\UsuarioRepository;
use App\Services\MenuService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private $propietarioRepository;
    private $empleadoRepository;
    private $usuarioRepository;
    private $emailService;
    private $menuService;

    public function __construct(
        PropietarioRepository $propietarioRepository,
        EmpleadoRepository $empleadoRepository,
        UsuarioRepository $usuarioRepository,
        EmailService $mailService,
        MenuService $menuService
    ) {
        $this->propietarioRepository = $propietarioRepository;
        $this->empleadoRepository = $empleadoRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->emailService = $mailService;
        $this->menuService = $menuService;
    }

    public function createUser(array $data)
    {
        // Logic to create a user
    }

    public function crearEmpleado( $request): Empleado
    {
        return DB::transaction(function () use ($request) {
            // Crear usuario
            $request->merge([
                'password' => Hash::make('12345678'),
                'tipo_usuario' => 'Empleado',
            ]);
            $usuario = $this->usuarioRepository->create($request->all());
            // Obtener propietario y restaurante
            $propietario = $this->propietarioRepository->findByUserId(auth()->user()->id);
            if (!$propietario) {
                throw new \Exception('Propietario no encontrado', 404);
            }

            $restaurante = \App\Repositories\RestauranteRepository::findRestauranteById($propietario->id_restaurante);
            // Crear empleado
            $empleado = $this->empleadoRepository->create($request, $usuario->id, $propietario->id, $propietario->id_restaurante);

            // Enviar correo
            $this->emailService->sendEmail($usuario->correo, new RegistroEmpleado($usuario, $restaurante));
            return $empleado;
        });
    }

    public function getUserById(int | null $id)
    {
        if ($id) {
            $usuario = $this->usuarioRepository->find($id);
            return $usuario ? $usuario : throw new \Exception('Usuario no encontrado', 404);
        } 

        throw new \Exception('El ID del usuario es requerido', 400);
    }

    public function updateUser(array $data)
    {
        $id = auth()->user()->id;
        $usuario = $this->usuarioRepository->update($id, $data);
        return $usuario ? $usuario : throw new \Exception('Usuario no encontrado', 404);
    }

    public function cambiarEstadoUsuarioConRol(string $id_usuario, bool $estado, string $rol)
    {
        DB::beginTransaction();
        try {
            $usuario = $this->cambiarEstadoUsuario($id_usuario, $estado);

            if ($rol === 'propietario') {
                $propietario = $this->propietarioRepository->findByUserId($usuario->id);
                $menu = $this->menuService->getMenuByRestaurantId($propietario->id_restaurante);
                $this->menuService->updateMenu($menu->id, ['disponible' => $estado]);
                $empleados = $this->empleadoRepository->getAllFrom($propietario->id);
                if ($empleados) {
                    foreach ($empleados as $empleado) {
                        $this->cambiarEstadoUsuario($empleado->id_usuario, $estado);
                    }
                }
            }
            DB::commit();
            return $usuario;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Propaga el error para que el controlador lo maneje
        }
    }

    private function cambiarEstadoUsuario(int $id, bool $estado)
    {
        $usuario = $this->usuarioRepository->update($id, ['estado' => $estado]);
        if ($usuario) {
            $email = $estado ? new AltaUsuario($usuario) : new BajaUsuario($usuario);
            if ($estado) {
                $usuario->tokens()->delete();
            }
            $this->emailService->sendEmail($usuario->correo, $email);
            return $usuario;
        }
        throw new \Exception('Usuario no encontrado', 404);
    }

    public function getPerfilUsuarios(string $rol)
    {

        switch ($rol) {
            case 'propietario':
                $usuarios = $this->propietarioRepository->all();
                break;
            case 'empleado':
                $id_propietario = auth()->user()->id;
                $usuarios = $this->empleadoRepository->getAllFrom($id_propietario);
                break;
            default:
                throw new \Exception('Rol de usuario no válido', 400);
        }
        return $usuarios;
    }
}

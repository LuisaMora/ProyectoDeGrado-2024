<?php

namespace App\Services;

use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\UsuarioRepository;

class UserService
{
    private $propietarioRepository;
    private $empleadoRepository;
    private $usuarioRepository;
    private $emailService;

    public function __construct(
        PropietarioRepository $propietarioRepository,
        EmpleadoRepository $empleadoRepository,
        UsuarioRepository $usuarioRepository,
        EmailService $mailService
    ) {
        $this->propietarioRepository = $propietarioRepository;
        $this->empleadoRepository = $empleadoRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->emailService = $mailService;
    }

    public function createUser(array $data)
    {
        // Logic to create a user
    }

    public function getUserById(int $id)
    {
        $usuario = $this->usuarioRepository->find($id);
        return $usuario ? $usuario : throw new \Exception('Usuario no encontrado', 404);
    }

    public function updateUser(int $id, array $data)
    {
        // Logic to update a user
    }

    public function cambiarEstadoUsuario(int $id, bool $estado)
    {
        $usuario = $this->usuarioRepository->update($id, ['estado' => $estado]);
        if ($usuario) {
            if ($estado) {
                $usuario->tokens()->delete();
                $this->emailService->sendEmail($usuario->correo,new AltaUsuario($usuario));
            } else {
                $this->emailService->sendEmail($usuario->correo,new BajaUsuario($usuario));
            }
            return $usuario;
        }
        throw new \Exception('Usuario no encontrado', 404);
    }

    public function cambiarEstadoUsuarioEmpleado(int $id_propietario, bool $estado)
    {
        $empleados = $this->empleadoRepository->getAllFrom($id_propietario);
        if ($empleados) {
            foreach ($empleados as $empleado) {
               $this->cambiarEstadoUsuario($empleado->id_usuario, $estado);
            }
            return $empleados;
        }
        throw new \Exception('No se pudo cambiar el estado de los empleados', 400);
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

    // Add more methods as needed
}

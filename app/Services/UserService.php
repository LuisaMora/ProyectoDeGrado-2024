<?php

namespace App\Services;

use App\Repositories\EmpleadoRepository;
use App\Repositories\PropietarioRepository;

class UserService
{
    private $propietarioRepository;
    private $empleadoRepository;
    public function __construct(PropietarioRepository $propietarioRepository, EmpleadoRepository $empleadoRepository)
    {
        $this->propietarioRepository = $propietarioRepository;
        $this->empleadoRepository = $empleadoRepository;
    }

    public function createUser(array $data)
    {
        // Logic to create a user
    }

    public function getUserById(int $id)
    {
        // Logic to get a user by ID
    }

    public function updateUser(int $id, array $data)
    {
        // Logic to update a user
    }

    public function deleteUser(int $id)
    {
        // Logic to delete a user
    }

    public function getPerfilUsuario(string $rol)
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
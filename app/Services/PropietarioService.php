<?php

namespace App\Services;

use App\Repositories\PropietarioRepository;

class PropietarioService
{

    private $propietarioRepository;

    public function __construct(PropietarioRepository $propietarioRepository)
    {
        $this->propietarioRepository = $propietarioRepository;
    }

    public function createPropietario(array $data)
    {
        // Logic to create a new propietario
    }

    public function getPropietarioByUserId($userId)
    {
        $propietario = $this->propietarioRepository->findByUserId($userId);
        return $propietario ? $propietario : throw new \Exception('Propietario no encontrado', 404);

    }

    public function updatePropietario($id, array $data)
    {
        // Logic to update a propietario by ID
    }

    public function deletePropietario($id)
    {
        // Logic to delete a propietario by ID
    }

    public function getAllPropietarios()
    {
        // Logic to retrieve all propietarios
    }
}
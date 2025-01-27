<?php

namespace App\Services;

use App\Repositories\EmpleadoRepository;
use App\Repositories\MenuRepository;
use App\Repositories\PropietarioRepository;

class MenuService
{
    private $menuRepository;
    private $propietarioRepository;
    private $empleadoRepository;

    public function __construct(
        MenuRepository $menuRepository,
        PropietarioRepository $propietarioRepository,
        EmpleadoRepository $empleadoRepository
    ) {
        $this->menuRepository = $menuRepository;
        $this->propietarioRepository = $propietarioRepository;
        $this->empleadoRepository = $empleadoRepository;
    }

    public function getMenuByRestaurantId(string $id_restaurante)
    {
        return $this->menuRepository->getMenuByRestaurantId($id_restaurante);
    }

    public function getMenuFromUser(string $userId, string $role)
    {
        // Logic to retrieve a menu by user ID
        // if ($role === 'propietario') {
        //     $id_restaurante = $this->propietarioRepository
        //         ->findByUserId($userId)->id_restaurante;
        // } elseif ($role === 'empleado') {
        //     $id_restaurante = $this->empleadoRepository
        //         ->findByUserId($userId)->id_restaurante;
        // }
        // return $this->menuRepository->getMenuByRestaurantId($id_restaurante);
    }

    public function getMenuProducts()
    {
        // Logic to retrieve menu items
    }

    public function addMenuProduct($item)
    {
        // Logic to add a new menu item
    }

    public function updateMenuProduct($id, $item)
    {
        // Logic to update an existing menu item
    }

    public function updateMenu($id, $data)
    {
        $this->menuRepository->update($id, $data);

    }

    public function hideMenuProduct($id)
    {
        // Logic to hide a menu item
    }

    public function deleteMenuProduct($id)
    {
        // Logic to delete a menu item
    }
}

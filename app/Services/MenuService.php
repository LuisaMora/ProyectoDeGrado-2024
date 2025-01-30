<?php

namespace App\Services;

use App\Models\Restaurante;
use App\Repositories\EmpleadoRepository;
use App\Repositories\MenuRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\RestauranteRepository;

class MenuService
{
    private $menuRepository;
    private $propietarioRepository;
    private $empleadoRepository;

    public function __construct(
        MenuRepository $menuRepository,
        PropietarioRepository $propietarioRepository,
        EmpleadoRepository $empleadoRepository,
        private ProductoRepository $productoRepository,
        private RestauranteRepository $restauranteRepository
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

    public function getMenuProducts($idRestaurante)
    {
        $idMenu =  $this->restauranteRepository->findRestauranteById($idRestaurante)->id_menu;
        if (!$idMenu) {
            throw new \Exception('Menu no encontrado.', 404);
        }
        return $this->productoRepository->getProductosMenu($idMenu);
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

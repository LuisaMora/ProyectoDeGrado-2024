<?php

namespace App\Services;

use App\Repositories\CategoriaRepository;
use App\Repositories\MenuRepository;
use App\Utils\ImageHandler;

class CategoriaService
{
    protected $categoriaRepository;
    protected $menuRepository;

    public function __construct(
        CategoriaRepository $categoriaRepository,
        MenuRepository $menuRepository
    ) {
        $this->categoriaRepository = $categoriaRepository;
        $this->menuRepository = $menuRepository;
    }

    public function getCategoriasByRestaurante($id_restaurante)
    {
        $menu = $this->menuRepository->getMenuByRestaurantId($id_restaurante.'');

        if (!$menu) {
            throw new \Exception('Menu no encontrado para el restaurante.', 404);
        }
        $id_menu = $menu->id;
        $categorias = $this->categoriaRepository->getCategoriasByMenuId($id_menu);

        if ($categorias->isEmpty()) {
            throw new \Exception('Categorias no encontradas para el menu.', 404);
        }

        return $categorias;
    }

    public function getCategoriaById($id)
    {
        $categoria = $this->categoriaRepository->getCategoriaById($id);

        if ($categoria == null) {
            throw new \Exception('Categoria no encontrada.', 404);
        }

        return $categoria;
    }

    public function createCategoria($data)
    {
        $menu = $this->menuRepository->getMenuByRestaurantId($data['id_restaurante']);

        if (!$menu) {
            throw new \Exception('Menu no encontrado', 404);
        }

        $imagen = ImageHandler::guardarArchivo($data['imagen'], 'categorias');

        $data['imagen'] = $imagen;
        $data['id_menu'] = $menu->id;

        return $this->categoriaRepository->create($data);
    }

    public function updateCategoria($id, $data)
    {
        if ((int)$id === 1) {
            throw new \Exception('No se puede editar la categoria por defecto.', 400);
        }

        $categoria = $this->categoriaRepository->getCategoriaById($id);

        if ($categoria == null) {
            throw new \Exception('Categoria no encontrada.', 404);
        }

        if (isset($data['imagen'])) {
            ImageHandler::eliminarArchivos([$categoria->imagen]);
            $data['imagen'] = ImageHandler::guardarArchivo($data['imagen'], 'categorias');
        }

        return $this->categoriaRepository->updateCategoria($id, $data);
    }

    public function deleteCategoria($id)
    {
        if ((int) $id === 1) {
            throw new \Exception('No se puede eliminar la categoria por defecto.', 400);
        }

        $categoria = $this->categoriaRepository->softDeleteCategoria($id);

        if (!$categoria) {
            throw new \Exception('Categoria no encontrada.', 404);
        }

        return $categoria;
    }
}

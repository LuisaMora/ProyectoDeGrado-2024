<?php

namespace App\Services;

use App\Repositories\CategoriaRepository;
use App\Repositories\RestauranteRepository;
use App\Utils\ImageHandler;

class CategoriaService
{
    protected $categoriaRepository;
    protected $restauranteRepository;

    public function __construct(
        CategoriaRepository $categoriaRepository,
        RestauranteRepository $restauranteRepository
    ) {
        $this->categoriaRepository = $categoriaRepository;
        $this->restauranteRepository = $restauranteRepository;
    }

    public function getCategoriasByRestaurante($id_restaurante)
    {
        $id_menu = $this->restauranteRepository->getMenuIdByRestauranteId($id_restaurante);

        if (!$id_menu) {
            throw new \Exception('Menu no encontrado para el restaurante.', 404);
        }

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
        $restaurante = $this->restauranteRepository->findRestauranteById($data['id_restaurante']);

        if (!$restaurante) {
            throw new \Exception('Restaurante no encontrado.', 404);
        }

        $imagen = ImageHandler::guardarArchivo($data['imagen'], 'categorias');

        $data['imagen'] = $imagen;
        $data['id_menu'] = $restaurante->id_menu;

        return $this->categoriaRepository->createCategoria($data);
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

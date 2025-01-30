<?php

namespace App\Services;

use App\Repositories\ProductoRepository;
use App\Models\Restaurante;
use App\Utils\ImageHandler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductoService
{
    protected $productoRepository;

    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    public function getProductosByRestaurante($id_restaurante, $soloDisponibles = false)
    {
        return $this->productoRepository->getProductosByRestaurante($id_restaurante, $soloDisponibles);
    }

    public function getProductoById($id)
    {
        return $this->productoRepository->getProductoById($id);
    }

    public function createProducto($data)
    {
        $restaurante = Restaurante::find($data['id_restaurante']);
        if (!$restaurante) {
            throw new \Exception('Restaurante no encontrado.', 404);
        }

        $data['id_menu'] = $restaurante->id_menu;
        $data['imagen'] = ImageHandler::guardarArchivo($data['imagen'], 'platillos');

        return $this->productoRepository->createProducto($data);
    }

    public function updateProducto($id, $data)
    {
        $platillo = $this->productoRepository->getProductoById($id);
        if (!$platillo) {
            throw new \Exception('Platillo no encontrado.', 404);
        }

        if (isset($data['imagen'])) {
            ImageHandler::eliminarArchivos([$platillo->imagen]);
            $data['imagen'] = ImageHandler::guardarArchivo($data['imagen'], 'platillos');
        }

        return $this->productoRepository->updateProducto($id, $data);
    }

    public function deleteProducto($id)
    {
        $platillo = $this->productoRepository->deleteProducto($id);
        if (!$platillo) {
            throw new \Exception('Platillo no encontrado.', 404);
        }

        // Borrar la imagen del storage
        $imagen = str_replace('storage', 'public', $platillo->imagen);
        Storage::delete($imagen);

        return $platillo;
    }
}

<?php

namespace App\Services;

use App\Repositories\ProductoRepository;
use App\Models\Restaurante;
use App\Utils\ImageHandler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductoService
{
    protected $platilloRepository;

    public function __construct(ProductoRepository $platilloRepository)
    {
        $this->platilloRepository = $platilloRepository;
    }

    public function getPlatillosByRestaurante($id_restaurante, $soloDisponibles = false)
    {
        return $this->platilloRepository->getPlatillosByRestaurante($id_restaurante, $soloDisponibles);
    }

    public function getPlatilloById($id)
    {
        return $this->platilloRepository->getPlatilloById($id);
    }

    public function createPlatillo($data)
    {
        $validarDatos = Validator::make($data, [
            'nombre' => 'required|max:100',
            'descripcion' => 'required|max:255',
            'precio' => 'required|numeric',
            'imagen' => 'required|image',
            'id_categoria' => 'required|numeric',
            'id_restaurante' => 'required|numeric',
        ]);

        if ($validarDatos->fails()) {
            throw new \Exception(json_encode($validarDatos->errors()), 422);
        }

        $restaurante = Restaurante::find($data['id_restaurante']);
        if (!$restaurante) {
            throw new \Exception('Restaurante no encontrado.', 404);
        }

        $data['id_menu'] = $restaurante->id_menu;
        $data['imagen'] = ImageHandler::guardarArchivo($data['imagen'], 'platillos');

        return $this->platilloRepository->createPlatillo($data);
    }

    public function updatePlatillo($id, $data)
    {
        $platillo = $this->platilloRepository->getPlatilloById($id);
        if (!$platillo) {
            throw new \Exception('Platillo no encontrado.', 404);
        }

        if (isset($data['imagen'])) {
            ImageHandler::eliminarArchivos([$platillo->imagen]);
            $data['imagen'] = ImageHandler::guardarArchivo($data['imagen'], 'platillos');
        }

        return $this->platilloRepository->updatePlatillo($id, $data);
    }

    public function deletePlatillo($id)
    {
        $platillo = $this->platilloRepository->deletePlatillo($id);
        if (!$platillo) {
            throw new \Exception('Platillo no encontrado.', 404);
        }

        // Borrar la imagen del storage
        $imagen = str_replace('storage', 'public', $platillo->imagen);
        Storage::delete($imagen);

        return $platillo;
    }
}

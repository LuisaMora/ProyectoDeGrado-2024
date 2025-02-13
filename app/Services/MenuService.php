<?php

namespace App\Services;

use App\Models\Restaurante;
use App\Repositories\EmpleadoRepository;
use App\Repositories\MenuRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\RestauranteRepository;
use App\Utils\ImageHandler;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;

class MenuService
{
    private $menuRepository;

    public function __construct(
        MenuRepository $menuRepository,
        private ProductoRepository $productoRepository,
        private PropietarioRepository $propietarioRepository,
        private RestauranteRepository $restauranteRepository
    ) {
        $this->menuRepository = $menuRepository;
    }

    public function getMenuByRestaurantId(string $id_restaurante)
    {
        $menu = $this->menuRepository->getMenuByRestaurantId($id_restaurante);
        if (!$menu) {
            throw new \Exception('Menú no encontrado.', 404);
        }
    
        return $menu;
    }

    public function getMenuById(string $id)
    {
        $menu = $this->menuRepository->findById($id);
        if (!$menu) {
            throw new \Exception('Menu no encontrado.', 404);
        }
    
        return $menu;
    }

    public function storeMenu($platilloStr, $idMenu, $datos)
    {
        $imagen = $datos->file('portada');

        // Validar JSON antes de decodificar
        $productos = json_decode($platilloStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Formato de platillos inválido', 400);
        }

        // Buscar el menú
        $menu = $this->menuRepository->findById($idMenu);
        if (!$menu) throw new \Exception('Menu no encontrado.', 404);

        // Guardar imagen si existe
        if ($imagen) {
            $nuevaPortada = ImageHandler::guardarArchivo($imagen, 'portadas');
            $datosArray = array_merge($datos->all(), ['portada' => $nuevaPortada]);
        } else {
            $datosArray = $datos->all();
        }

        // Actualizar menú
        $menu = $this->menuRepository->update($idMenu, $datosArray);

        // Actualizar platillos
        foreach ($productos as $prodMenu) {
            $dataProducto = ['plato_disponible_menu' => $prodMenu['plato_disponible_menu']];
            $this->productoRepository->updateProducto($prodMenu['id'], $dataProducto);
        }

        return $menu;
    }


    public function generateQr(string $dirUrl)
    {
        $propietario = $this->propietarioRepository->findByUserId(auth()->user()->id);
        $menu = $this->menuRepository->getMenuByRestaurantId($propietario->id_restaurante);

        $tiempo = time() . '_' . $menu->id;

        // Verificar si la carpeta existe, y si no, crearla
        $qrDirectory = storage_path('app/public/codigos_qr');
        if (!File::exists($qrDirectory)) {
            File::makeDirectory($qrDirectory, 0755, true); // Crear la carpeta con permisos
        }

        $path = $qrDirectory . '/qr_' . $tiempo . '.png';

        $writer = new PngWriter();
        $qrCode = QrCode::create($dirUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $label = Label::create('Escanee el código QR')
            ->setTextColor(new Color(186, 50, 23));

        // Generar el código QR
        $result = $writer->write($qrCode, null, $label);
        $result->saveToFile($path);

        // Guardar la URL del código QR en la base de datos
        $url_codigo_qr = '/storage/codigos_qr/qr_' . $tiempo . '.png';
        $menuData = ['qr' => $url_codigo_qr];
        $this->menuRepository->update($menu->id, $menuData);
        return $url_codigo_qr;
    }


    public function getMenuProducts($idRestaurante = 0, $filtrarPorDisponibilidad = true, $idMenu = 0)
    {
        $idMenu =  $idMenu == 0 ? $this->restauranteRepository->findRestauranteById($idRestaurante)->id_menu : $idMenu;
        if (!$idMenu) {
            throw new \Exception('Menu no encontrado.', 404);
        }
        return $this->productoRepository->getProductosMenu($idMenu, $filtrarPorDisponibilidad);
    }


    public function updateMenu($id, $data)
    {
        $this->menuRepository->update($id, $data);
    }

}

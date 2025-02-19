<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateQrRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Services\MenuService;
use Exception;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index($idRestaurante)
    {
        try {
            $menu = $this->menuService->getMenuByRestaurantId($idRestaurante);
            $platillos = $this -> menuService -> getMenuProducts(filtrarPorDisponibilidad:false, idMenu:$menu->id);
            return response()->json(['status' => 'success', 'menu' => $menu, 'platillos' => $platillos], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $menu = $this->menuService->getMenuById($id);
            $productos = $this->menuService->getMenuProducts(idMenu: $id);
            return response()->json(['status' => 'success', 'menu' => $menu, 'platillos' => $productos], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function storeMenu(StoreMenuRequest $request)
    {
        try {
            $idMenu = $request->id_menu;
            $platillos = $request->platillos;
            $menu = $this->menuService->storeMenu($platillos, $idMenu, $request);
            return response()->json(['status' => 'success', 'menu' => $menu], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function generateQr(GenerateQrRequest $request)
    {
        try {
            $dirUrl = $request->direccion_url_menu;
            $url_codigo_qr = $this->menuService->generateQr($dirUrl);
            return response()->json(['status' => 'success', 'qr' => $url_codigo_qr], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}

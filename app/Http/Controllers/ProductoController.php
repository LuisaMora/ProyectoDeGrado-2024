<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroProductoRequest;
use App\Services\MenuService;
use App\Services\ProductoService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function __construct(private ProductoService $platilloService, private MenuService $menuService)
    { 
    }

    public function index($id_restaurante)
    {
        $platillos = $this->platilloService->getPlatillosByRestaurante($id_restaurante);
        return response()->json(['status' => 'success', 'platillos' => $platillos], 200);
    }

    public function platillosDisponibles($id_restaurante)
    {
        $platillos = $this->menuService->getMenuProducts($id_restaurante);
        return response()->json(['status' => 'success', 'platillo' => $platillos], 200);
    }

    public function store(RegistroProductoRequest $request)
    {
        try {
            $platillo = $this->platilloService->createPlatillo($request->all());
            return response()->json(['status' => 'success', 'platillo' => $platillo,
                'message' => 'Plato registrado exitosamente!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function show($id)
    {
        $platillo = $this->platilloService->getPlatilloById($id);
        if (!$platillo) {
            return response()->json(['message' => 'Platillo no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $platillo = $this->platilloService->updatePlatillo($id, $request->all());
            return response()->json(['status' => 'success', 'platillo' => $platillo], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->platilloService->deletePlatillo($id);
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroProductoRequest;
use App\Services\MenuService;
use App\Services\ProductoService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function __construct(private ProductoService $productoService, private MenuService $menuService)
    { 
    }

    public function index($id_restaurante)
    {
        $productos = $this->productoService->getProductosByRestaurante($id_restaurante);
        return response()->json(['status' => 'success', 'platillos' => $productos], 200);
    }

    public function productosDisponibles($id_restaurante)
    {
        $productos = $this->menuService->getMenuProducts($id_restaurante);
        return response()->json(['status' => 'success', 'platillo' => $productos], 200);
    }

    public function store(RegistroProductoRequest $request)
    {
        try {
            $producto = $this->productoService->createProducto($request->all());
            return response()->json(['status' => 'success', 'platillo' => $producto,
                'message' => 'Plato registrado exitosamente!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function show($id)
    {
        $producto = $this->productoService->getProductoById($id);
        if (!$producto) {
            return response()->json(['message' => 'Platillo no encontrado.'], 404);
        }
        return response()->json(['status' => 'success', 'platillo' => $producto], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = $this->productoService->updateProducto($id, $request->all());
            return response()->json(['status' => 'success', 'platillo' => $producto], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->productoService->deleteProducto($id);
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}

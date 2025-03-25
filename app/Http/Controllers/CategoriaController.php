<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function index($id_restaurante)
    {
        try {
            $categorias = $this->categoriaService->getCategoriasByRestaurante($id_restaurante);
            return response()->json(['status' => 'success', 'categorias' => $categorias], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function store(CategoriaRequest $request)
    {

        try {
            $categoria = $this->categoriaService->createCategoria($request->all());
            return response()->json(['status' => 'success', 'categoria' => $categoria], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode()<600 && $e->getCode()>199 ?$e->getCode(): 500);
        }
    }

    public function show($id)
    {
        try {
            $categoria = $this->categoriaService->getCategoriaById($id); 
            return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $categoria = $this->categoriaService->updateCategoria($id, $request->all());
            return response()->json(['status' => 'success', 'categoria' => $categoria], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $categoria = $this->categoriaService->deleteCategoria($id);
            return response()->json(['status' => 'success', 'message' => 'Categoria eliminada.', 'categoria' => $categoria], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }
}

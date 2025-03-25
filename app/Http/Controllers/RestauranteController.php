<?php

namespace App\Http\Controllers;

use App\Repositories\RestauranteRepository;
use App\Services\AuthService;

class RestauranteController extends Controller
{
    public function __construct(private AuthService $authService, private RestauranteRepository $restauranteRepository)
    {
    }
    public function show()
    {
        try {
            $usuario = auth()->user();
            $idRestauante = $this->authService->getDatosPersonales($usuario->id, $usuario->tipo_usuario)->id_restaurante;
            $restaurante = $this->restauranteRepository->findRestauranteById($idRestauante);
            return response()->json(['restaurante' => $restaurante], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}

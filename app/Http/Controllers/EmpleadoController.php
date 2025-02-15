<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpleadoRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class EmpleadoController extends Controller
{

    public function __construct(private UserService $userService)
    {
    }

    public function store(StoreEmpleadoRequest $request): JsonResponse
    {
        try {
            $empleado = $this->userService->crearEmpleado($request);
            return response()->json(['status' => 'success', 'data' => $empleado], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode()<600 && $e->getCode()>199 ?$e->getCode(): 500);
        }
    }
}

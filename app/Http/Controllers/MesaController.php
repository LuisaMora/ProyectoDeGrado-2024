<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MesaService;

class MesaController extends Controller
{
    public function __construct(private MesaService $mesaService)
    {        
    }

    public function index($idRestaurante)
    {
        try {
            $mesas = $this->mesaService->getMesasByIdRest($idRestaurante);    
            return response()->json(['status' => 'success', 'mesas' => $mesas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        }
    }
}
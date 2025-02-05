<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReporteRequest;
use App\Services\ReporteService;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $reporteService)
    {
    }

    public function getReporte(ReporteRequest $request)
    {
        $data = $this->reporteService->getReporte($request
        );

        return response()->json($data, 200);
    }
}

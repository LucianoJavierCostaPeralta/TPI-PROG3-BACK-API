<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EstadoEntregaService;

class EstadoEntregaController extends Controller
{
    protected EstadoEntregaService $estadoService;

    public function __construct(EstadoEntregaService $estadoService)
    {
        $this->estadoService = $estadoService;
    }

    /**
     * Display a listing of the resource (read-only catalog).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $estados = $this->estadoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Estados de entrega retrieved successfully',
            'data' => $estados
        ], 200);
    }
}

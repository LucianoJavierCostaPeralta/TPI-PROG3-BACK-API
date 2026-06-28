<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DetalleEntregaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DetalleEntregaController extends Controller
{
    protected DetalleEntregaService $detalleService;

    public function __construct(DetalleEntregaService $detalleService)
    {
        $this->detalleService = $detalleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $detalles = $this->detalleService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Detalles de entrega retrieved successfully',
            'data' => $detalles
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $detalle = $this->detalleService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Detalle de entrega created successfully',
                'data' => $detalle
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}

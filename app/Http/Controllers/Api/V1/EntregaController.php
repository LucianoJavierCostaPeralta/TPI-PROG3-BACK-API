<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EntregaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EntregaController extends Controller
{
    protected EntregaService $entregaService;

    public function __construct(EntregaService $entregaService)
    {
        $this->entregaService = $entregaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $entregas = $this->entregaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Entregas retrieved successfully',
            'data' => $entregas
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
            $entrega = $this->entregaService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Entrega created successfully',
                'data' => $entrega
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

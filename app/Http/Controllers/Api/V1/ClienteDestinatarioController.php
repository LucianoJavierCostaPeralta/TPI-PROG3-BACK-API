<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ClienteDestinatarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClienteDestinatarioController extends Controller
{
    protected ClienteDestinatarioService $clienteService;

    public function __construct(ClienteDestinatarioService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $clientes = $this->clienteService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Clientes destinatarios retrieved successfully',
            'data' => $clientes
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
            $cliente = $this->clienteService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario created successfully',
                'data' => $cliente
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

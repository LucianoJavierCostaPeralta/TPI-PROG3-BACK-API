<?php

namespace App\Http\Controllers\Api\V1\Logistica;

use App\Http\Controllers\Controller;
use App\Services\ClienteDestinatarioService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
     */
    public function index(): JsonResponse
    {
        $clientes = $this->clienteService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Clientes destinatarios retrieved successfully',
            'data' => $clientes,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario created successfully',
                'data' => $cliente,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->getById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario retrieved successfully',
                'data' => $cliente,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->update($request->all(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario updated successfully',
                'data' => $cliente,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->clienteService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario deleted successfully',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found',
            ], 404);
        }
    }
}

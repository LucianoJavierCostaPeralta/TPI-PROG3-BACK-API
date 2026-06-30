<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ClienteDestinatarioService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $cliente = $this->clienteService->getById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario retrieved successfully',
                'data' => $cliente
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $cliente = $this->clienteService->update($request->all(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario updated successfully',
                'data' => $cliente
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->clienteService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente destinatario deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente destinatario not found'
            ], 404);
        }
    }
}

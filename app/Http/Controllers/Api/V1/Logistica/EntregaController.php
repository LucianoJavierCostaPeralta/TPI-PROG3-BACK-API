<?php

namespace App\Http\Controllers\Api\V1\Logistica;

use App\Http\Controllers\Controller;
use App\Services\EntregaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $entrega = $this->entregaService->getById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Entrega retrieved successfully',
                'data' => $entrega
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entrega not found'
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
            $entrega = $this->entregaService->update($request->all(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Entrega updated successfully',
                'data' => $entrega
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entrega not found'
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
            $this->entregaService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Entrega deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entrega not found'
            ], 404);
        }
    }
}

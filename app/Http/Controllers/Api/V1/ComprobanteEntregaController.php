<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ComprobanteEntregaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ComprobanteEntregaController extends Controller
{
    protected ComprobanteEntregaService $comprobanteService;

    public function __construct(ComprobanteEntregaService $comprobanteService)
    {
        $this->comprobanteService = $comprobanteService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $comprobantes = $this->comprobanteService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Comprobantes de entrega retrieved successfully',
            'data' => $comprobantes
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
            $comprobante = $this->comprobanteService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Comprobante de entrega created successfully',
                'data' => $comprobante
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
            $comprobante = $this->comprobanteService->getById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Comprobante de entrega retrieved successfully',
                'data' => $comprobante
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comprobante de entrega not found'
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
            $comprobante = $this->comprobanteService->update($request->all(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Comprobante de entrega updated successfully',
                'data' => $comprobante
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comprobante de entrega not found'
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
            $this->comprobanteService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Comprobante de entrega deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comprobante de entrega not found'
            ], 404);
        }
    }
}

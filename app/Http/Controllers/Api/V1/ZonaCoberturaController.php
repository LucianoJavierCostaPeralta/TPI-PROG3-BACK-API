<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ZonaCoberturaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las zonas de cobertura - Autor: Ulises
class ZonaCoberturaController extends Controller
{
    protected ZonaCoberturaService $zonaService;

    public function __construct(ZonaCoberturaService $zonaService)
    {
        $this->zonaService = $zonaService;
    }

    /**
     * Listar todas las zonas de cobertura disponibles.
     * Incluye información de la empresa asociada.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $zonas = $this->zonaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Zonas de cobertura retrieved successfully',
            'data' => $zonas
        ], 200);
    }

    /**
     * Crear una nueva zona de cobertura.
     * El código postal debe ser único para la empresa.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $zona = $this->zonaService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Zona de cobertura created successfully',
                'data' => $zona
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $zona = $this->zonaService->getById($id);
            return response()->json(['status' => 'success', 'message' => 'Zona de cobertura retrieved successfully', 'data' => $zona], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Zona de cobertura not found'], 404);
        }
    }

    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $zona = $this->zonaService->update($request->all(), $id);
            return response()->json(['status' => 'success', 'message' => 'Zona de cobertura updated successfully', 'data' => $zona], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Zona de cobertura not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->zonaService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Zona de cobertura deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Zona de cobertura not found'], 404);
        }
    }
}

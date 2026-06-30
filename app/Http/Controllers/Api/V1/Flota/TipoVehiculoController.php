<?php

namespace App\Http\Controllers\Api\V1\Flota;

use App\Http\Controllers\Controller;
use App\Services\TipoVehiculoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los tipos de vehículos - Autor: Ulises
class TipoVehiculoController extends Controller
{
    protected TipoVehiculoService $tipoService;

    public function __construct(TipoVehiculoService $tipoService)
    {
        $this->tipoService = $tipoService;
    }

    /**
     * Listar todos los tipos de vehículos disponibles.
     * Este endpoint es de solo lectura ya que los tipos son catálogos fijos del sistema.
     */
    public function index(): JsonResponse
    {
        $tipos = $this->tipoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Tipos de vehículo retrieved successfully',
            'data' => $tipos,
        ], 200);
    }

    /**
     * Crear un nuevo tipo de vehículo.
     * Nota: Este endpoint debería usarse solo durante la configuración inicial del sistema.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $tipo = $this->tipoService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de vehículo created successfully',
                'data' => $tipo,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $tipo = $this->tipoService->getById($id);

            return response()->json(['status' => 'success', 'message' => 'Tipo retrieved successfully', 'data' => $tipo], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Tipo not found'], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $tipo = $this->tipoService->update($request->all(), $id);

            return response()->json(['status' => 'success', 'message' => 'Tipo updated successfully', 'data' => $tipo], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Tipo not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->tipoService->delete($id);

            return response()->json(['status' => 'success', 'message' => 'Tipo deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Tipo not found'], 404);
        }
    }
}

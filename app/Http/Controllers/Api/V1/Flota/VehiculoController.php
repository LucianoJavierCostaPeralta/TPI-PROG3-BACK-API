<?php

namespace App\Http\Controllers\Api\V1\Flota;

use App\Http\Controllers\Controller;
use App\Services\VehiculoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los vehículos de la flota - Autor: Ulises
class VehiculoController extends Controller
{
    protected VehiculoService $vehiculoService;

    public function __construct(VehiculoService $vehiculoService)
    {
        $this->vehiculoService = $vehiculoService;
    }

    /**
     * Listar todos los vehículos disponibles.
     * Incluye información de la empresa y tipo de vehículo asociado.
     */
    public function index(): JsonResponse
    {
        $vehiculos = $this->vehiculoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehículos retrieved successfully',
            'data' => $vehiculos,
        ], 200);
    }

    /**
     * Registrar un nuevo vehículo en la flota.
     * La patente debe ser única en todo el sistema.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'tipo_id' => 'required|integer|exists:tipos_vehiculo,id',
            'patente' => 'required|string|max:20|unique:vehiculos,patente',
            'marca_modelo' => 'required|string|max:255',
            'estado_operativo' => 'sometimes|boolean',
        ]);

        $vehiculo = $this->vehiculoService->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Vehículo created successfully',
            'data' => $vehiculo,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->getById($id);

            return response()->json(['status' => 'success', 'message' => 'Vehículo retrieved successfully', 'data' => $vehiculo], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Vehículo not found'], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->update($request->all(), $id);

            return response()->json(['status' => 'success', 'message' => 'Vehículo updated successfully', 'data' => $vehiculo], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Vehículo not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->vehiculoService->delete($id);

            return response()->json(['status' => 'success', 'message' => 'Vehículo deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Vehículo not found'], 404);
        }
    }
}

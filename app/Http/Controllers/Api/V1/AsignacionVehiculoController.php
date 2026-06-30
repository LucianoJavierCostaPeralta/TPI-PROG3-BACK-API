<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AsignacionVehiculoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las asignaciones de vehículos - Autor: Ulises
class AsignacionVehiculoController extends Controller
{
    protected AsignacionVehiculoService $asignacionService;

    public function __construct(AsignacionVehiculoService $asignacionService)
    {
        $this->asignacionService = $asignacionService;
    }

    /**
     * Listar todas las asignaciones de vehículos activas.
     * Incluye información del chofer y vehículo asignado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $asignaciones = $this->asignacionService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Asignaciones de vehículos retrieved successfully',
            'data' => $asignaciones
        ], 200);
    }

    /**
     * Asignar un vehículo a un chofer.
     * La fecha_fin es opcional (null indica que la asignación sigue vigente).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $asignacion = $this->asignacionService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Asignación de vehículo created successfully',
                'data' => $asignacion
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
            $asignacion = $this->asignacionService->getById($id);
            return response()->json(['status' => 'success', 'message' => 'Asignación retrieved successfully', 'data' => $asignacion], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Asignación not found'], 404);
        }
    }

    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $asignacion = $this->asignacionService->update($request->all(), $id);
            return response()->json(['status' => 'success', 'message' => 'Asignación updated successfully', 'data' => $asignacion], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Asignación not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->asignacionService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Asignación deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Asignación not found'], 404);
        }
    }
}

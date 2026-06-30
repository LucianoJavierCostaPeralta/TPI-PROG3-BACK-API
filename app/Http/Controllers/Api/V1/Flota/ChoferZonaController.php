<?php

namespace App\Http\Controllers\Api\V1\Flota;

use App\Http\Controllers\Controller;
use App\Services\ChoferZonaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las asignaciones de zonas a choferes - Autor: Ulises
class ChoferZonaController extends Controller
{
    protected ChoferZonaService $choferZonaService;

    public function __construct(ChoferZonaService $choferZonaService)
    {
        $this->choferZonaService = $choferZonaService;
    }

    /**
     * Listar todas las relaciones chofer-zona.
     * Incluye información del chofer y la zona asignada.
     */
    public function index(): JsonResponse
    {
        $relaciones = $this->choferZonaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Relaciones chofer-zona retrieved successfully',
            'data' => $relaciones,
        ], 200);
    }

    /**
     * Asignar una zona a un chofer.
     * Valida que ambos IDs existan antes de crear la relación.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->choferZonaService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Relación chofer-zona created successfully',
                'data' => null,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(string $usuarioId, string $zonaId): JsonResponse
    {
        try {
            $relacion = $this->choferZonaService->getById($usuarioId, $zonaId);

            return response()->json(['status' => 'success', 'message' => 'Relación chofer-zona retrieved successfully', 'data' => $relacion], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Relación chofer-zona not found'], 404);
        }
    }

    public function destroy(string $usuarioId, string $zonaId): JsonResponse
    {
        try {
            $this->choferZonaService->delete($usuarioId, $zonaId);

            return response()->json(['status' => 'success', 'message' => 'Relación chofer-zona deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Relación chofer-zona not found'], 404);
        }
    }
}

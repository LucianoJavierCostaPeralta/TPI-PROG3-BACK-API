<?php

namespace App\Http\Controllers\Api\V1\Soporte;

use App\Http\Controllers\Controller;
use App\Services\AuditoriaLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los logs de auditoría - Autor: Ulises
class AuditoriaLogController extends Controller
{
    protected AuditoriaLogService $auditoriaLogService;

    public function __construct(AuditoriaLogService $auditoriaLogService)
    {
        $this->auditoriaLogService = $auditoriaLogService;
    }

    /**
     * Listar todos los registros de auditoría.
     * Incluye información del usuario responsable de la acción.
     */
    public function index(): JsonResponse
    {
        $logs = $this->auditoriaLogService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Auditoria logs retrieved successfully',
            'data' => $logs,
        ], 200);
    }

    /**
     * Crear un nuevo registro de auditoría.
     * El campo detalle_json debe ser un array válido.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usuario_id' => 'nullable|uuid|exists:users,id',
            'tabla_afectada' => 'required|string|max:255',
            'accion' => 'required|string|max:50',
            'detalle_json' => 'nullable|array',
            'fecha_evento' => 'nullable|date',
        ]);

        $log = $this->auditoriaLogService->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Auditoria log created successfully',
            'data' => $log,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $log = $this->auditoriaLogService->getById($id);

            return response()->json(['status' => 'success', 'message' => 'Auditoria log retrieved successfully', 'data' => $log], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Auditoria log not found'], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $log = $this->auditoriaLogService->update($request->all(), $id);

            return response()->json(['status' => 'success', 'message' => 'Auditoria log updated successfully', 'data' => $log], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Auditoria log not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->auditoriaLogService->delete($id);

            return response()->json(['status' => 'success', 'message' => 'Auditoria log deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Auditoria log not found'], 404);
        }
    }
}

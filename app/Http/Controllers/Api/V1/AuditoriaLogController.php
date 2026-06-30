<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuditoriaLogService;
use Illuminate\Http\Request;

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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $logs = $this->auditoriaLogService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Auditoria logs retrieved successfully',
            'data' => $logs
        ], 200);
    }

    /**
     * Crear un nuevo registro de auditoría.
     * El campo detalle_json debe ser un array válido.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Usamos $request->validate() para una validación más directa
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
            'data' => $log
        ], 201);
    }
}

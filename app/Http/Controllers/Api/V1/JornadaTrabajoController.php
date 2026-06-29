<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\JornadaTrabajoService;
use Illuminate\Http\Request;

// Controlador encargado de gestionar las jornadas de trabajo - Autor: Ulises
class JornadaTrabajoController extends Controller
{
    protected JornadaTrabajoService $jornadaService;

    public function __construct(JornadaTrabajoService $jornadaService)
    {
        $this->jornadaService = $jornadaService;
    }

    /**
     * Listar todas las jornadas de trabajo.
     * Incluye información del chofer asociado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $jornadas = $this->jornadaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Jornadas de trabajo retrieved successfully',
            'data' => $jornadas
        ], 200);
    }

    /**
     * Registrar una nueva jornada de trabajo.
     * La distancia_recorrida_km se inicializa en 0.00 por defecto.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Usamos $request->validate() para una validación más directa
        $validated = $request->validate([
            'usuario_id' => 'required|uuid|exists:users,id',
            'fecha_jornada' => 'required|date',
            'hora_inicio' => 'nullable|date',
            'hora_fin' => 'nullable|date|after:hora_inicio',
            'distancia_recorrida_km' => 'sometimes|numeric|min:0|max:999999.99',
        ]);

        $jornada = $this->jornadaService->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jornada de trabajo created successfully',
            'data' => $jornada
        ], 201);
    }
}

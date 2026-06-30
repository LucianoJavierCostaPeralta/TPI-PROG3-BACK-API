<?php

namespace App\Http\Controllers\Api\V1\Flota;

use App\Http\Controllers\Controller;
use App\Services\JornadaTrabajoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jornada = $this->jornadaService->getById($id);
            return response()->json(['status' => 'success', 'message' => 'Jornada de trabajo retrieved successfully', 'data' => $jornada], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Jornada de trabajo not found'], 404);
        }
    }

    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jornada = $this->jornadaService->update($request->all(), $id);
            return response()->json(['status' => 'success', 'message' => 'Jornada de trabajo updated successfully', 'data' => $jornada], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Jornada de trabajo not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->jornadaService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Jornada de trabajo deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Jornada de trabajo not found'], 404);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ChoferZonaService;
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $relaciones = $this->choferZonaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Relaciones chofer-zona retrieved successfully',
            'data' => $relaciones
        ], 200);
    }

    /**
     * Asignar una zona a un chofer.
     * Valida que ambos IDs existan antes de crear la relación.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->choferZonaService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Relación chofer-zona created successfully',
                'data' => null
            ], 201);

        } catch (ValidationException $e) {
            // Retornamos errores de validación con formato estándar
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}

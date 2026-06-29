<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MotivoRechazoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los motivos de rechazo - Autor: Ulises
class MotivoRechazoController extends Controller
{
    protected MotivoRechazoService $motivoService;

    public function __construct(MotivoRechazoService $motivoService)
    {
        $this->motivoService = $motivoService;
    }

    /**
     * Listar todos los motivos de rechazo disponibles.
     * Este endpoint es de solo lectura ya que los motivos son catálogos fijos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $motivos = $this->motivoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Motivos de rechazo retrieved successfully',
            'data' => $motivos
        ], 200);
    }

    /**
     * Crear un nuevo motivo de rechazo.
     * Nota: Este endpoint debería usarse solo durante la configuración inicial del sistema.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $motivo = $this->motivoService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Motivo de rechazo created successfully',
                'data' => $motivo
            ], 201);

        } catch (ValidationException $e) {
            // Manejamos las excepciones de validación con formato estándar
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}

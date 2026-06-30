<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SolicitudAsesoramientoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las solicitudes de asesoramiento - Autor: Ulises
class SolicitudAsesoramientoController extends Controller
{
    protected SolicitudAsesoramientoService $solicitudService;

    public function __construct(SolicitudAsesoramientoService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    /**
     * Listar todas las solicitudes de asesoramiento.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $solicitudes = $this->solicitudService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitudes de asesoramiento retrieved successfully',
            'data' => $solicitudes
        ], 200);
    }

    /**
     * Crear una nueva solicitud de asesoramiento.
     * El campo leido se inicializa en false por defecto.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $solicitud = $this->solicitudService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitud de asesoramiento created successfully',
                'data' => $solicitud
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

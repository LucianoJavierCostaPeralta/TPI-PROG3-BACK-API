<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las notificaciones del sistema - Autor: Ulises
class NotificacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Listar todas las notificaciones del sistema.
     * Incluye información del usuario asociado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $notificaciones = $this->notificacionService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Notificaciones retrieved successfully',
            'data' => $notificaciones
        ], 200);
    }

    /**
     * Crear una nueva notificación.
     * El campo leida se inicializa en false por defecto.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $notificacion = $this->notificacionService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Notificacion created successfully',
                'data' => $notificacion
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

<?php

namespace App\Http\Controllers\Api\V1\Soporte;

use App\Http\Controllers\Controller;
use App\Services\NotificacionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las notificaciones del sistema - Autor: Ulises
class NotificacionController extends Controller
{
    public function __construct(protected NotificacionService $notificacionService)
    {
    }

    /**
     * Listar las notificaciones del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $notificaciones = $this->notificacionService->getForUser($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Notificaciones retrieved successfully',
            'data' => $notificaciones,
        ], 200);
    }

    /**
     * Crear una nueva notificación.
     * El campo leida se inicializa en false por defecto.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $notificacion = $this->notificacionService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Notificacion created successfully',
                'data' => $notificacion,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $notificacion = $this->notificacionService->getByIdForUser($request->user(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Notificacion retrieved successfully',
                'data' => $notificacion,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Notificacion not found'], 404);
        }
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        try {
            $notificacion = $this->notificacionService->markAsRead($request->user(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Notificacion marked as read successfully',
                'data' => $notificacion,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Notificacion not found'], 404);
        }
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $this->notificacionService->markAllAsRead($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Notificaciones marked as read successfully',
            'data' => [
                'updated' => $updated,
            ],
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $notificacion = $this->notificacionService->update($request->all(), $id);

            return response()->json(['status' => 'success', 'message' => 'Notificacion updated successfully', 'data' => $notificacion], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Notificacion not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->notificacionService->delete($id);

            return response()->json(['status' => 'success', 'message' => 'Notificacion deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Notificacion not found'], 404);
        }
    }
}

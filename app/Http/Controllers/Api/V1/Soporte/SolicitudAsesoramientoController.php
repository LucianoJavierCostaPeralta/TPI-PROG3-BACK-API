<?php

namespace App\Http\Controllers\Api\V1\Soporte;

use App\Http\Controllers\Controller;
use App\Services\NotificacionService;
use App\Services\SolicitudAsesoramientoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar las solicitudes de asesoramiento - Autor: Ulises
class SolicitudAsesoramientoController extends Controller
{
    public function __construct(
        protected SolicitudAsesoramientoService $solicitudService,
        protected NotificacionService $notificacionService,
    ) {
    }

    /**
     * Listar todas las solicitudes de asesoramiento.
     */
    public function index(): JsonResponse
    {
        $solicitudes = $this->solicitudService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitudes de asesoramiento retrieved successfully',
            'data' => $solicitudes,
        ], 200);
    }

    /**
     * Crear una nueva solicitud de asesoramiento.
     * El campo leido se inicializa en false por defecto.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $solicitud = $this->solicitudService->createForUser(
            $user,
            $datos['mensaje'],
        );

        $this->notificacionService->createForCompanyAdmins(
            $user,
            'Nueva solicitud de asesoramiento',
            sprintf('%s envió una nueva solicitud de asesoramiento.', $user->nombre_completo),
            'info',
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Solicitud de asesoramiento creada correctamente',
            'data' => $solicitud,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $solicitud = $this->solicitudService->getById($id);

            return response()->json(['status' => 'success', 'message' => 'Solicitud de asesoramiento retrieved successfully', 'data' => $solicitud], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Solicitud de asesoramiento not found'], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $solicitud = $this->solicitudService->update($request->all(), $id);

            return response()->json(['status' => 'success', 'message' => 'Solicitud de asesoramiento updated successfully', 'data' => $solicitud], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Solicitud de asesoramiento not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->solicitudService->delete($id);

            return response()->json(['status' => 'success', 'message' => 'Solicitud de asesoramiento deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Solicitud de asesoramiento not found'], 404);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntregaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entregas = Entrega::query()
            ->where('chofer_id', $request->user()->id)
            ->with([
                'cliente:id,nombre_completo,telefono,direccion_frecuente',
                'estado:id,nombre_estado',
            ])
            ->latest()
            ->get();

        return response()->json([
            'data' => $entregas,
        ]);
    }

    public function show(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        return response()->json([
            'data' => $entrega->load([
                'cliente:id,nombre_completo,telefono,direccion_frecuente',
                'estado:id,nombre_estado',
            ]),
        ]);
    }

    public function accept(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        if ($entrega->estado_id !== Entrega::ESTADO_ASSIGNED) {
            return response()->json([
                'message' => 'Solo se pueden aceptar entregas asignadas.',
            ], 422);
        }

        $entrega->update([
            'estado_id' => Entrega::ESTADO_ACCEPTED,
        ]);

        return response()->json([
            'message' => 'Entrega aceptada correctamente.',
            'data' => $entrega->load('estado:id,nombre_estado'),
        ]);
    }

    public function state(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        $data = $request->validate([
            'estado_id' => ['required', 'integer', Rule::in(Entrega::DRIVER_ESTADOS)],
        ]);

        if (! $this->canMoveToEstado($entrega->estado_id, $data['estado_id'])) {
            return response()->json([
                'message' => 'Cambio de estado no permitido para esta entrega.',
            ], 422);
        }

        $entrega->update([
            'estado_id' => $data['estado_id'],
        ]);

        return response()->json([
            'message' => 'Estado de la entrega actualizado correctamente.',
            'data' => $entrega->load('estado:id,nombre_estado'),
        ]);
    }

    private function ensureAssignedToChofer(Request $request, Entrega $entrega): void
    {
        abort_if($entrega->chofer_id !== $request->user()->id, 404, 'Entrega no encontrada.');
    }

    private function canMoveToEstado(int $currentEstadoId, int $nextEstadoId): bool
    {
        return match ($currentEstadoId) {
            Entrega::ESTADO_ACCEPTED => $nextEstadoId === Entrega::ESTADO_ON_THE_WAY,
            Entrega::ESTADO_ON_THE_WAY => $nextEstadoId === Entrega::ESTADO_DELIVERED,
            Entrega::ESTADO_DELIVERED => $nextEstadoId === Entrega::ESTADO_FINISHED,
            default => false,
        };
    }
}

<?php

namespace App\Http\Controllers\Api\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnvioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $envios = Envio::query()
            ->where('chofer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $envios,
        ]);
    }

    public function accept(Request $request, Envio $envio): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $envio);

        if ($envio->state !== Envio::STATE_ASSIGNED) {
            return response()->json([
                'message' => 'Solo se pueden aceptar envios asignados.',
            ], 422);
        }

        $envio->update([
            'state' => Envio::STATE_ACCEPTED,
        ]);

        return response()->json([
            'message' => 'Envio aceptado correctamente.',
            'data' => $envio,
        ]);
    }

    public function state(Request $request, Envio $envio): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $envio);

        $data = $request->validate([
            'state' => ['required', Rule::in(Envio::DRIVER_STATES)],
        ]);

        if (! $this->canMoveToState($envio->state, $data['state'])) {
            return response()->json([
                'message' => 'Cambio de estado no permitido para este envio.',
            ], 422);
        }

        $envio->update([
            'state' => $data['state'],
        ]);

        return response()->json([
            'message' => 'Estado del envio actualizado correctamente.',
            'data' => $envio,
        ]);
    }

    private function ensureAssignedToChofer(Request $request, Envio $envio): void
    {
        abort_if($envio->chofer_id !== $request->user()->id, 404, 'Envio no encontrado.');
    }

    private function canMoveToState(string $currentState, string $nextState): bool
    {
        return match ($currentState) {
            Envio::STATE_ACCEPTED => $nextState === Envio::STATE_ON_THE_WAY,
            Envio::STATE_ON_THE_WAY => $nextState === Envio::STATE_DELIVERED,
            Envio::STATE_DELIVERED => $nextState === Envio::STATE_FINISHED,
            default => false,
        };
    }
}

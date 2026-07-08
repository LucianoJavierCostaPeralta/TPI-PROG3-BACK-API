<?php

namespace App\Http\Controllers\Api\V1\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Services\AuditoriaLogService;
use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** @tags Chofer - Entregas */
class EntregaController extends Controller
{
    public function __construct(
        private readonly AuditoriaLogService $auditoria,
        private readonly NotificacionService $notificaciones,
    ) {}

    /** Listar las entregas asignadas al chofer. */
    public function index(Request $request): JsonResponse
    {
        $entregas = Entrega::query()
            ->where('chofer_id', $request->user()->id)
            ->with([
                'estado:id,nombre_estado',
            ])
            ->latest()
            ->get();
        $entregas->makeHidden('cliente_dni');

        return response()->json([
            'data' => $entregas,
        ]);
    }

    /** Consultar una entrega asignada y su historial. */
    public function show(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        return response()->json([
            'data' => $entrega->load([
                'estado:id,nombre_estado',
                'historialEstados.estadoAnterior:id,nombre_estado',
                'historialEstados.estadoNuevo:id,nombre_estado',
                'historialEstados.usuario:id,nombre_completo',
            ])->makeHidden('cliente_dni'),
        ]);
    }

    /** Aceptar una entrega asignada. */
    public function accept(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        if ($entrega->estado_id !== Entrega::ESTADO_ASSIGNED) {
            return response()->json([
                'message' => 'Solo se pueden aceptar entregas asignadas.',
            ], 422);
        }

        $estadoAnteriorId = $entrega->estado_id;

        DB::transaction(function () use ($request, $entrega, $estadoAnteriorId): void {
            $entrega->update([
                'estado_id' => Entrega::ESTADO_ACCEPTED,
            ]);

            $entrega->historialEstados()->create([
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => Entrega::ESTADO_ACCEPTED,
                'usuario_id' => $request->user()->id,
                'fecha_cambio' => now(),
            ]);
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $entrega, 'entregas', 'entrega.accepted', [
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => Entrega::ESTADO_ACCEPTED,
            ]);

            $this->notificaciones->createForCompanyAdmins(
                $request->user(),
                'Entrega aceptada',
                sprintf('El chofer %s aceptó la entrega #%s.', $request->user()->nombre_completo, strtoupper(substr((string) $entrega->id, 0, 8))),
                'success',
            );
        });

        return response()->json([
            'message' => 'Entrega aceptada correctamente.',
            'data' => $entrega->load('estado:id,nombre_estado')->makeHidden('cliente_dni'),
        ]);
    }

    /** Avanzar el estado; delivered exige el DNI del cliente. */
    public function state(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureAssignedToChofer($request, $entrega);

        $data = $request->validate([
            'estado_id' => ['required', 'integer', Rule::in(Entrega::DRIVER_ESTADOS)],
            'cliente_dni' => [
                'exclude_unless:estado_id,'.Entrega::ESTADO_DELIVERED,
                'required',
                'string',
                'regex:/^\d{8}$/',
            ],
        ]);

        if (! $this->canMoveToEstado($entrega->estado_id, $data['estado_id'])) {
            return response()->json([
                'message' => 'Cambio de estado no permitido para esta entrega.',
            ], 422);
        }

        if ($data['estado_id'] === Entrega::ESTADO_DELIVERED
            && ! hash_equals((string) $entrega->cliente_dni, $data['cliente_dni'])) {
            throw ValidationException::withMessages([
                'cliente_dni' => ['El DNI no coincide con el cliente de la entrega.'],
            ]);
        }

        $estadoAnteriorId = $entrega->estado_id;

        DB::transaction(function () use ($request, $entrega, $data, $estadoAnteriorId): void {
            $entrega->update([
                'estado_id' => $data['estado_id'],
            ]);

            $entrega->historialEstados()->create([
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => $data['estado_id'],
                'usuario_id' => $request->user()->id,
                'fecha_cambio' => now(),
            ]);
            $accion = $data['estado_id'] === Entrega::ESTADO_ON_THE_WAY
                ? 'entrega.on_the_way'
                : 'entrega.delivered';
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $entrega, 'entregas', $accion, [
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => $data['estado_id'],
            ]);

            $shortId = strtoupper(substr((string) $entrega->id, 0, 8));
            if ($data['estado_id'] === Entrega::ESTADO_ON_THE_WAY) {
                $this->notificaciones->createForCompanyAdmins(
                    $request->user(),
                    'Entrega en camino',
                    sprintf('El chofer %s marcó en camino la entrega #%s.', $request->user()->nombre_completo, $shortId),
                    'info',
                );
            }

            if ($data['estado_id'] === Entrega::ESTADO_DELIVERED) {
                $this->notificaciones->createForCompanyAdmins(
                    $request->user(),
                    'Entrega finalizada',
                    sprintf('El chofer %s finalizó la entrega #%s.', $request->user()->nombre_completo, $shortId),
                    'success',
                );
            }
        });

        return response()->json([
            'message' => 'Estado de la entrega actualizado correctamente.',
            'data' => $entrega->load('estado:id,nombre_estado')->makeHidden('cliente_dni'),
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
            default => false,
        };
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EntregaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'estado_id' => ['sometimes', 'integer', Rule::exists('estado_entregas', 'id')],
            'chofer_id' => [
                'sometimes',
                'uuid',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('rol_id', User::ROL_CHOFER)
                    ->where('empresa_id', $request->user()->empresa_id)),
            ],
            'sin_chofer' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if (($data['sin_chofer'] ?? false) && isset($data['chofer_id'])) {
            throw ValidationException::withMessages([
                'chofer_id' => ['No se puede combinar chofer_id con sin_chofer.'],
            ]);
        }

        $entregas = Entrega::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->when(isset($data['estado_id']), fn ($query) => $query->where('estado_id', $data['estado_id']))
            ->when(isset($data['chofer_id']), fn ($query) => $query->where('chofer_id', $data['chofer_id']))
            ->when($data['sin_chofer'] ?? false, fn ($query) => $query->whereNull('chofer_id'))
            ->with([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'estado:id,nombre_estado',
            ])
            ->latest()
            ->paginate($data['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'data' => $entregas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'cliente' => ['required', 'string', 'min:2', 'max:150'],
            'producto' => ['required', 'string', 'min:2', 'max:150'],
            'direccion_destino' => ['required', 'string', 'min:3', 'max:255'],
            'orden_ruta' => ['nullable', 'integer', 'min:1'],
            'referencia' => ['nullable', 'string', 'max:500'],
        ]);

        $entrega = Entrega::create([
            'empresa_id' => $admin->empresa_id,
            'cliente' => $data['cliente'],
            'producto' => $data['producto'],
            'estado_id' => Entrega::ESTADO_PENDING,
            'direccion_destino' => $data['direccion_destino'],
            'orden_ruta' => $data['orden_ruta'] ?? null,
            'referencia' => $data['referencia'] ?? null,
        ]);

        return response()->json([
            'message' => 'Entrega creada correctamente.',
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'estado:id,nombre_estado',
            ]),
        ], 201);
    }

    public function show(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureEntregaBelongsToEmpresa($request, $entrega);

        return response()->json([
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'estado:id,nombre_estado',
                'historialEstados.estadoAnterior:id,nombre_estado',
                'historialEstados.estadoNuevo:id,nombre_estado',
                'historialEstados.usuario:id,nombre_completo',
            ]),
        ]);
    }

    public function assign(Request $request, Entrega $entrega): JsonResponse
    {
        $this->ensureEntregaBelongsToEmpresa($request, $entrega);

        $data = $request->validate([
            'chofer_id' => [
                'present',
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($request) {
                    $query
                        ->where('rol_id', User::ROL_CHOFER)
                        ->where('empresa_id', $request->user()->empresa_id);
                }),
            ],
        ]);

        if (! in_array($entrega->estado_id, [Entrega::ESTADO_PENDING, Entrega::ESTADO_ASSIGNED], true)) {
            return response()->json([
                'message' => 'Solo se pueden asignar o desasignar entregas pendientes o asignadas.',
            ], 422);
        }

        $choferId = $data['chofer_id'] ?? null;
        $isUnassigning = $choferId === null;

        $estadoAnteriorId = $entrega->estado_id;
        $estadoNuevoId = $isUnassigning ? Entrega::ESTADO_PENDING : Entrega::ESTADO_ASSIGNED;

        DB::transaction(function () use (
            $request,
            $entrega,
            $choferId,
            $isUnassigning,
            $estadoAnteriorId,
            $estadoNuevoId
        ): void {
            $entrega->update([
                'chofer_id' => $choferId,
                'estado_id' => $estadoNuevoId,
                'fecha_asignacion' => $isUnassigning ? null : now(),
            ]);

            if ($estadoAnteriorId !== $estadoNuevoId) {
                $entrega->historialEstados()->create([
                    'estado_anterior_id' => $estadoAnteriorId,
                    'estado_nuevo_id' => $estadoNuevoId,
                    'usuario_id' => $request->user()->id,
                    'fecha_cambio' => now(),
                ]);
            }
        });

        return response()->json([
            'message' => $isUnassigning
                ? 'Entrega desasignada correctamente.'
                : 'Entrega asignada correctamente.',
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'estado:id,nombre_estado',
            ]),
        ]);
    }

    private function ensureEntregaBelongsToEmpresa(Request $request, Entrega $entrega): void
    {
        abort_if($entrega->empresa_id !== $request->user()->empresa_id, 404, 'Entrega no encontrada.');
    }
}

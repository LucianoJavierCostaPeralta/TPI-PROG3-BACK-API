<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntregaController extends Controller
{
    public function index(): JsonResponse
    {
        $entregas = Entrega::query()
            ->with([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'cliente:id,nombre_completo,telefono',
                'estado:id,nombre_estado',
            ])
            ->latest()
            ->get();

        return response()->json([
            'data' => $entregas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'cliente_id' => [
                'required',
                'uuid',
                Rule::exists('clientes_destinatarios', 'id')->where(
                    fn ($query) => $query->where('empresa_id', $admin->empresa_id)
                ),
            ],
            'direccion_destino' => ['required', 'string', 'min:3', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'orden_ruta' => ['nullable', 'integer', 'min:1'],
            'referencia' => ['nullable', 'string', 'max:500'],
        ]);

        $entrega = Entrega::create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => $data['cliente_id'],
            'estado_id' => Entrega::ESTADO_PENDING,
            'direccion_destino' => $data['direccion_destino'],
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'orden_ruta' => $data['orden_ruta'] ?? null,
            'referencia' => $data['referencia'] ?? null,
        ]);

        return response()->json([
            'message' => 'Entrega creada correctamente.',
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'cliente:id,nombre_completo,telefono',
                'estado:id,nombre_estado',
            ]),
        ], 201);
    }

    public function show(Entrega $entrega): JsonResponse
    {
        return response()->json([
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'cliente:id,nombre_completo,telefono,direccion_frecuente',
                'estado:id,nombre_estado',
            ]),
        ]);
    }

    public function assign(Request $request, Entrega $entrega): JsonResponse
    {
        $data = $request->validate([
            'chofer_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where('rol_id', User::ROL_CHOFER),
            ],
        ]);

        if (! in_array($entrega->estado_id, [Entrega::ESTADO_PENDING, Entrega::ESTADO_ASSIGNED], true)) {
            return response()->json([
                'message' => 'Solo se pueden asignar entregas pendientes o asignadas.',
            ], 422);
        }

        $entrega->update([
            'chofer_id' => $data['chofer_id'],
            'estado_id' => Entrega::ESTADO_ASSIGNED,
            'fecha_asignacion' => now(),
        ]);

        return response()->json([
            'message' => 'Entrega asignada correctamente.',
            'data' => $entrega->load([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'cliente:id,nombre_completo,telefono',
                'estado:id,nombre_estado',
            ]),
        ]);
    }
}

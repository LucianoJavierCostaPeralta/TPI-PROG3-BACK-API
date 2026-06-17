<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnvioController extends Controller
{
    public function index(): JsonResponse
    {
        $envios = Envio::query()
            ->with('chofer:id,name,apellido,dni,email,telefono,role')
            ->latest()
            ->get();

        return response()->json([
            'data' => $envios,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'direccion_origen' => ['required', 'string', 'min:3', 'max:255'],
            'direccion_destino' => ['required', 'string', 'min:3', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $envio = Envio::create([
            'direccion_origen' => $data['direccion_origen'],
            'direccion_destino' => $data['direccion_destino'],
            'descripcion' => $data['descripcion'] ?? null,
            'state' => Envio::STATE_PENDING,
        ]);

        return response()->json([
            'message' => 'Envio creado correctamente.',
            'data' => $envio->load('chofer:id,name,apellido,dni,email,telefono,role'),
        ], 201);
    }

    public function assign(Request $request, Envio $envio): JsonResponse
    {
        $data = $request->validate([
            'chofer_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'chofer')],
        ]);

        if (! in_array($envio->state, [Envio::STATE_PENDING, Envio::STATE_ASSIGNED], true)) {
            return response()->json([
                'message' => 'Solo se pueden asignar envios pendientes o asignados.',
            ], 422);
        }

        $envio->update([
            'chofer_id' => $data['chofer_id'],
            'state' => Envio::STATE_ASSIGNED,
        ]);

        return response()->json([
            'message' => 'Envio asignado correctamente.',
            'data' => $envio->load('chofer:id,name,apellido,dni,email,telefono,role'),
        ]);
    }
}

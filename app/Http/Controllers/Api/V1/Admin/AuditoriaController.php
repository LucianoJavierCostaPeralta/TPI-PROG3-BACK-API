<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** @tags Administracion - Auditoria */
class AuditoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'accion' => ['sometimes', 'string', 'max:50'],
            'usuario_id' => ['sometimes', 'uuid'],
            'recurso' => ['sometimes', 'string', 'max:255'],
            'recurso_id' => ['sometimes', 'uuid'],
            'desde' => ['sometimes', 'date'],
            'hasta' => ['sometimes', 'date', 'after_or_equal:desde'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $logs = AuditoriaLog::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->when(isset($data['accion']), fn ($query) => $query->where('accion', $data['accion']))
            ->when(isset($data['usuario_id']), fn ($query) => $query->where('usuario_id', $data['usuario_id']))
            ->when(isset($data['recurso']), fn ($query) => $query->where('tabla_afectada', $data['recurso']))
            ->when(isset($data['recurso_id']), fn ($query) => $query->where('recurso_id', $data['recurso_id']))
            ->when(isset($data['desde']), fn ($query) => $query->where('fecha_evento', '>=', $data['desde']))
            ->when(isset($data['hasta']), fn ($query) => $query->where('fecha_evento', '<=', $data['hasta']))
            ->with('usuario:id,nombre_completo,email')
            ->latest('fecha_evento')
            ->paginate($data['per_page'] ?? 15)
            ->withQueryString();

        return response()->json(['data' => $logs]);
    }

    public function show(Request $request, AuditoriaLog $auditoria): JsonResponse
    {
        abort_if($auditoria->empresa_id !== $request->user()->empresa_id, 404, 'Auditoria no encontrada.');

        return response()->json(['data' => $auditoria->load('usuario:id,nombre_completo,email')]);
    }
}

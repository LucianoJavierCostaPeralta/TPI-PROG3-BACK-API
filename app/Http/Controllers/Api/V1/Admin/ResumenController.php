<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entrega;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** @tags Administracion - Resumen */
class ResumenController extends Controller
{
    /** Resumen de la pantalla principal del administrador. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing([
            'rol:id,nombre_rol',
            'empresa',
        ]);

        $empresaId = $user->empresa_id;

        $admins = User::query()
            ->where('empresa_id', $empresaId)
            ->where('rol_id', User::ROL_ADMIN)
            ->with('rol:id,nombre_rol')
            ->orderBy('nombre_completo')
            ->get();

        $drivers = User::query()
            ->where('empresa_id', $empresaId)
            ->where('rol_id', User::ROL_CHOFER)
            ->with('rol:id,nombre_rol')
            ->orderBy('nombre_completo')
            ->get();

        $orders = Entrega::query()
            ->where('empresa_id', $empresaId)
            ->with([
                'chofer:id,nombre_completo,email,telefono,rol_id',
                'estado:id,nombre_estado',
            ])
            ->latest()
            ->get();

        return response()->json([
            'data' => [
                'profile' => $user,
                'company' => $user->empresa,
                'drivers' => $drivers,
                'orders' => $orders,
                'admins' => $admins,
            ],
        ]);
    }
}

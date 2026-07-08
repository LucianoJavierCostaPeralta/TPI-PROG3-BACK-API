<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditoriaLogService;
use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** @tags Administracion - Choferes */
class ChoferController extends Controller
{
    public function __construct(
        private readonly AuditoriaLogService $auditoria,
        private readonly NotificacionService $notificaciones,
    ) {}

    /** Listar los choferes de la empresa. */
    public function index(Request $request): JsonResponse
    {
        $choferes = User::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('rol_id', User::ROL_CHOFER)
            ->with('rol:id,nombre_rol')
            ->orderBy('nombre_completo')
            ->get();

        return response()->json([
            'data' => $choferes,
        ]);
    }

    /** Crear un chofer en la empresa. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'min:3', 'max:255', "regex:/^[\pL\s]+$/u"],
            'dni' => ['required', 'string', "regex:/^\d{8}$/", 'unique:users,dni'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $chofer = DB::transaction(function () use ($request, $data): User {
            $chofer = User::create([
                'empresa_id' => $request->user()->empresa_id,
                'rol_id' => User::ROL_CHOFER,
                'nombre_completo' => $data['nombre_completo'],
                'dni' => $data['dni'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => $data['password'],
                'activo' => $data['activo'] ?? true,
            ]);
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $chofer, 'choferes', 'chofer.created', [
                'nombre_completo' => $chofer->nombre_completo,
                'email' => $chofer->email,
                'activo' => $chofer->activo,
            ]);
            $this->notificaciones->createForUser(
                $chofer,
                'Tu cuenta fue creada',
                sprintf('Tu cuenta de chofer fue creada para la empresa %s.', $request->user()->empresa?->razon_social ?? 'la empresa'),
                'success',
            );

            return $chofer;
        });

        return response()->json([
            'message' => 'Chofer creado correctamente.',
            'data' => $chofer->load('rol:id,nombre_rol'),
        ], 201);
    }

    /** Consultar un chofer de la empresa. */
    public function show(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($request, $chofer);

        return response()->json([
            'data' => $chofer->load('rol:id,nombre_rol'),
        ]);
    }

    /** Actualizar un chofer de la empresa. */
    public function update(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($request, $chofer);

        $data = $request->validate([
            'nombre_completo' => ['sometimes', 'required', 'string', 'min:3', 'max:255', "regex:/^[\pL\s]+$/u"],
            'dni' => [
                'sometimes',
                'required',
                'string',
                "regex:/^\d{8}$/",
                Rule::unique('users', 'dni')->ignore($chofer->id),
            ],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($chofer->id),
            ],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:20'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $chofer, $data): void {
            $chofer->update($data);
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $chofer, 'choferes', 'chofer.updated', [
                'campos_modificados' => array_values(array_diff(array_keys($data), ['dni'])),
                'dni_modificado' => array_key_exists('dni', $data),
            ]);
            $this->notificaciones->createForUser(
                $chofer,
                'Tus datos fueron actualizados',
                'Un administrador actualizó tu perfil de chofer.',
                'info',
            );
        });

        return response()->json([
            'message' => 'Chofer actualizado correctamente.',
            'data' => $chofer->load('rol:id,nombre_rol'),
        ]);
    }

    /** Restablecer la contrasena de un chofer. */
    public function resetPassword(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($request, $chofer);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($request, $chofer, $data): void {
            $chofer->update(['password' => $data['password']]);
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $chofer, 'choferes', 'chofer.password_reset');
            $this->notificaciones->createForUser(
                $chofer,
                'Tu contraseña fue restablecida',
                'Un administrador restableció tu contraseña de acceso.',
                'warning',
            );
        });

        return response()->json([
            'message' => 'Contrasena actualizada correctamente.',
        ]);
    }

    /** Eliminar un chofer de la empresa. */
    public function destroy(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($request, $chofer);

        DB::transaction(function () use ($request, $chofer): void {
            $this->auditoria->record($request->user()->empresa_id, $request->user(), $chofer, 'choferes', 'chofer.deleted', [
                'nombre_completo' => $chofer->nombre_completo,
                'email' => $chofer->email,
            ]);
            $chofer->delete();
        });

        return response()->json([
            'message' => 'Chofer eliminado correctamente.',
        ]);
    }

    private function ensureChofer(Request $request, User $user): void
    {
        abort_if(
            ! $user->isChofer() || $user->empresa_id !== $request->user()->empresa_id,
            404,
            'Chofer no encontrado.'
        );
    }
}

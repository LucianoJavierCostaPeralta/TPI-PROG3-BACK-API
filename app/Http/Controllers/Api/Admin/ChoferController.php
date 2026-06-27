<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChoferController extends Controller
{
    public function index(): JsonResponse
    {
        $choferes = User::query()
            ->where('rol_id', User::ROL_CHOFER)
            ->with('rol:id,nombre_rol')
            ->orderBy('nombre_completo')
            ->get();

        return response()->json([
            'data' => $choferes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $chofer = User::create([
            'empresa_id' => $request->user()->empresa_id,
            'rol_id' => User::ROL_CHOFER,
            'nombre_completo' => $data['nombre_completo'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'password' => $data['password'],
            'activo' => $data['activo'] ?? true,
        ]);

        return response()->json([
            'message' => 'Chofer creado correctamente.',
            'data' => $chofer->load('rol:id,nombre_rol'),
        ], 201);
    }

    public function show(User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        return response()->json([
            'data' => $chofer->load('rol:id,nombre_rol'),
        ]);
    }

    public function update(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        $data = $request->validate([
            'nombre_completo' => ['sometimes', 'required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
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

        $chofer->update($data);

        return response()->json([
            'message' => 'Chofer actualizado correctamente.',
            'data' => $chofer->load('rol:id,nombre_rol'),
        ]);
    }

    public function resetPassword(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $chofer->update([
            'password' => $data['password'],
        ]);

        return response()->json([
            'message' => 'Contrasena actualizada correctamente.',
        ]);
    }

    public function destroy(User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        $chofer->delete();

        return response()->json([
            'message' => 'Chofer eliminado correctamente.',
        ]);
    }

    private function ensureChofer(User $user): void
    {
        abort_if(! $user->isChofer(), 404, 'Chofer no encontrado.');
    }
}

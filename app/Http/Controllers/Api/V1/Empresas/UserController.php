<?php

namespace App\Http\Controllers\Api\V1\Empresas;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->with('rol')
            ->orderBy('nombre_completo')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $data['empresa_id'] = $request->user()->empresa_id;
        $data['activo'] ??= true;

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user->load('rol'),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $this->user($request, $id)->load('rol'),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = $this->user($request, $id);
        $user->update($this->validatedData($request, $user));

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->load('rol'),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->user($request, $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }

    private function user(Request $request, string $id): User
    {
        return User::query()
            ->whereKey($id)
            ->where('empresa_id', $request->user()->empresa_id)
            ->firstOrFail();
    }

    private function validatedData(Request $request, ?User $user = null): array
    {
        $sometimes = $user ? 'sometimes' : 'required';

        return $request->validate([
            'empresa_id' => ['prohibited'],
            'rol_id' => [$sometimes, 'integer', Rule::in([User::ROL_ADMIN])],
            'nombre_completo' => [$sometimes, 'string', 'max:255'],
            'dni' => ['sometimes', 'nullable', 'string', 'regex:/^\d{8}$/', Rule::unique('users', 'dni')->ignore($user?->id)],
            'fecha_nacimiento' => ['sometimes', 'nullable', 'date', 'before:today'],
            'email' => [$sometimes, 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$sometimes, 'string', 'min:8'],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:20'],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }
}

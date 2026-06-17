<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ChoferController extends Controller
{
    public function index(): JsonResponse
    {
        $choferes = User::query()
            ->where('role', 'chofer')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $choferes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'dni' => ['required', 'digits:8', 'unique:users,dni'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'licencia' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9-]+$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'digits_between:6,20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $chofer = User::create([
            'name' => $data['name'],
            'apellido' => $data['apellido'],
            'dni' => $data['dni'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'licencia' => $data['licencia'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'password' => Hash::make($data['password']),
            'role' => 'chofer',
        ]);

        return response()->json([
            'message' => 'Chofer creado correctamente.',
            'data' => $chofer,
        ], 201);
    }

    public function show(User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        return response()->json([
            'data' => $chofer,
        ]);
    }

    public function update(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['sometimes', 'required', 'string', 'min:3', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'dni' => [
                'sometimes',
                'required',
                'digits:8',
                Rule::unique('users', 'dni')->ignore($chofer->id),
            ],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
            'licencia' => ['sometimes', 'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9-]+$/'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($chofer->id),
            ],
            'telefono' => ['sometimes', 'required', 'digits_between:6,20'],
        ]);

        $chofer->update($data);

        return response()->json([
            'message' => 'Chofer actualizado correctamente.',
            'data' => $chofer,
        ]);
    }

    public function resetPassword(Request $request, User $chofer): JsonResponse
    {
        $this->ensureChofer($chofer);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $chofer->update([
            'password' => Hash::make($data['password']),
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
        abort_if($user->role !== 'chofer', 404, 'Chofer no encontrado.');
    }
}

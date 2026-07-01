<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** @tags Autenticacion */
class AuthController extends Controller
{
    /** Iniciar sesion y obtener un token Sanctum. */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->with('rol:id,nombre_rol')
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            throw ValidationException::withMessages([
                'email' => ['La cuenta se encuentra inactiva.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /** Consultar el perfil autenticado. */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('rol:id,nombre_rol', 'empresa:id,razon_social'),
        ]);
    }

    /** Cerrar la sesion actual. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesion cerrada correctamente.',
        ]);
    }
}

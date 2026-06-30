<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

// Controlador encargado de gestionar los usuarios del sistema - Autor: Ulises
class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Listar todos los usuarios del sistema.
     * Incluye información de la empresa y rol asociado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = $this->userService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }

    /**
     * Registrar un nuevo usuario en el sistema.
     * El email debe ser único y el password se encripta automáticamente.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Usamos $request->validate() para una validación más directa
        $validated = $request->validate([
            'empresa_id' => 'nullable|uuid|exists:empresas,id',
            'rol_id' => 'required|integer|exists:roles,id',
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean',
        ]);

        $user = $this->userService->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }
}

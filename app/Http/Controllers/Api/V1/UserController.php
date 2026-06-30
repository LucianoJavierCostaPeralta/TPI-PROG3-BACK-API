<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $this->userService->getById($id);
            return response()->json(['status' => 'success', 'message' => 'User retrieved successfully', 'data' => $user], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
    }

    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $this->userService->update($request->all(), $id);
            return response()->json(['status' => 'success', 'message' => 'User updated successfully', 'data' => $user], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->userService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'User deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
    }
}

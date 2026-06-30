<?php

namespace App\Http\Controllers\Api\V1\Empresas;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los roles del sistema - Autor: Ulises
class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Listar todos los roles disponibles.
     * Este endpoint es de solo lectura ya que los roles son catálogos fijos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $roles = $this->roleService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ], 200);
    }

    /**
     * Crear un nuevo rol.
     * Nota: Este endpoint debería usarse solo durante la configuración inicial del sistema.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $rol = $this->roleService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully',
                'data' => $rol
            ], 201);

        } catch (ValidationException $e) {
            // Manejamos las excepciones de validación con formato estándar
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}

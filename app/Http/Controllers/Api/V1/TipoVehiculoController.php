<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TipoVehiculoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Controlador encargado de gestionar los tipos de vehículos - Autor: Ulises
class TipoVehiculoController extends Controller
{
    protected TipoVehiculoService $tipoService;

    public function __construct(TipoVehiculoService $tipoService)
    {
        $this->tipoService = $tipoService;
    }

    /**
     * Listar todos los tipos de vehículos disponibles.
     * Este endpoint es de solo lectura ya que los tipos son catálogos fijos del sistema.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tipos = $this->tipoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Tipos de vehículo retrieved successfully',
            'data' => $tipos
        ], 200);
    }

    /**
     * Crear un nuevo tipo de vehículo.
     * Nota: Este endpoint debería usarse solo durante la configuración inicial del sistema.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $tipo = $this->tipoService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Tipo de vehículo created successfully',
                'data' => $tipo
            ], 201);

        } catch (ValidationException $e) {
            // Retornamos los errores de validación con formato estándar
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\VehiculoService;
use Illuminate\Http\Request;

// Controlador encargado de gestionar los vehículos de la flota - Autor: Ulises
class VehiculoController extends Controller
{
    protected VehiculoService $vehiculoService;

    public function __construct(VehiculoService $vehiculoService)
    {
        $this->vehiculoService = $vehiculoService;
    }

    /**
     * Listar todos los vehículos disponibles.
     * Incluye información de la empresa y tipo de vehículo asociado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $vehiculos = $this->vehiculoService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehículos retrieved successfully',
            'data' => $vehiculos
        ], 200);
    }

    /**
     * Registrar un nuevo vehículo en la flota.
     * La patente debe ser única en todo el sistema.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Usamos $request->validate() para una validación más directa
        $validated = $request->validate([
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'tipo_id' => 'required|integer|exists:tipos_vehiculo,id',
            'patente' => 'required|string|max:20|unique:vehiculos,patente',
            'marca_modelo' => 'required|string|max:255',
            'estado_operativo' => 'sometimes|boolean',
        ]);

        $vehiculo = $this->vehiculoService->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Vehículo created successfully',
            'data' => $vehiculo
        ], 201);
    }
}

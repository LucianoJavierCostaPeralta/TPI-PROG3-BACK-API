<?php

namespace App\Services;

use App\Models\Vehiculo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para Vehiculo - Autor: Ulises
class VehiculoService
{
    protected Vehiculo $vehiculo;

    public function __construct(Vehiculo $vehiculo)
    {
        $this->vehiculo = $vehiculo;
    }

    /**
     * Crear un nuevo vehículo con validación completa.
     * Validamos que la patente no esté duplicada antes de guardar.
     *
     * @param array $data
     * @return Vehiculo
     * @throws ValidationException
     */
    public function create(array $data): Vehiculo
    {
        // Nota: el estado_operativo lo inicializamos en true por defecto si no se proporciona
        if (!isset($data['estado_operativo'])) {
            $data['estado_operativo'] = true;
        }

        // TODO: Añadir validación de capacidad para el tipo de vehículo
        $validator = Validator::make($data, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'tipo_id' => 'required|integer|exists:tipos_vehiculo,id',
            'patente' => 'required|string|max:20|unique:vehiculos,patente',
            'marca_modelo' => 'required|string|max:255',
            'estado_operativo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->vehiculo->create($data);
    }

    /**
     * Obtener todos los vehículos del sistema.
     * Incluye las relaciones con empresa y tipo para optimizar consultas.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        // Cargamos eager loading para evitar N+1 queries
        return $this->vehiculo->with(['empresa', 'tipo'])->get();
    }
}

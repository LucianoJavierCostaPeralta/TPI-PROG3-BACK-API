<?php

namespace App\Services;

use App\Models\TipoVehiculo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para TipoVehiculo - Autor: Ulises
class TipoVehiculoService
{
    protected TipoVehiculo $tipo;

    public function __construct(TipoVehiculo $tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * Crear un nuevo tipo de vehículo con validación.
     * Usamos Validator::make para tener control granular sobre los mensajes de error.
     *
     * @param array $payload
     * @return TipoVehiculo
     * @throws ValidationException
     */
    public function create(array $payload): TipoVehiculo
    {
        // Validamos que la capacidad sea un valor positivo y razonable
        $validator = Validator::make($payload, [
            'nombre_tipo' => 'required|string|max:100|unique:tipos_vehiculo,nombre_tipo',
            'capacidad_kg' => 'required|numeric|min:0|max:999999.99',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->tipo->create($payload);
    }

    /**
     * Obtener todos los tipos de vehículos (catálogo de lectura).
     * Este método es de solo lectura ya que los tipos son catálogos fijos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tipo->all();
    }
}

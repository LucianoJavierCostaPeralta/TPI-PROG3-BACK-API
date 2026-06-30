<?php

namespace App\Services;

use App\Models\AsignacionVehiculo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para AsignacionVehiculo - Autor: Ulises
class AsignacionVehiculoService
{
    protected AsignacionVehiculo $asignacion;

    public function __construct(AsignacionVehiculo $asignacion)
    {
        $this->asignacion = $asignacion;
    }

    /**
     * Crear una nueva asignación de vehículo a chofer.
     * Validamos que el vehículo y el usuario existan antes de asignar.
     *
     * @param array $atributos
     * @return AsignacionVehiculo
     * @throws ValidationException
     */
    public function create(array $atributos): AsignacionVehiculo
    {
        // Validamos que las fechas sean lógicas: inicio debe ser anterior a fin si ambas están presentes
        $validator = Validator::make($atributos, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'vehiculo_id' => 'required|uuid|exists:vehiculos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->asignacion->create($atributos);
    }

    /**
     * Obtener todas las asignaciones de vehículos.
     * Incluye las relaciones con usuario y vehículo para mostrar información completa.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->asignacion->with(['usuario', 'vehiculo'])->get();
    }

    public function getById(string $id): AsignacionVehiculo
    {
        return $this->asignacion->with(['usuario', 'vehiculo'])->findOrFail($id);
    }

    public function update(array $atributos, string $id): AsignacionVehiculo
    {
        $asignacion = $this->asignacion->findOrFail($id);
        $validator = Validator::make($atributos, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'vehiculo_id' => 'required|uuid|exists:vehiculos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $asignacion->update($atributos);
        return $asignacion;
    }

    public function delete(string $id): bool
    {
        $asignacion = $this->asignacion->findOrFail($id);
        return $asignacion->delete();
    }
}

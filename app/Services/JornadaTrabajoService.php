<?php

namespace App\Services;

use App\Models\JornadaTrabajo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para JornadaTrabajo - Autor: Ulises
class JornadaTrabajoService
{
    protected JornadaTrabajo $jornada;

    public function __construct(JornadaTrabajo $jornada)
    {
        $this->jornada = $jornada;
    }

    /**
     * Crear una nueva jornada de trabajo con validación.
     * Nota: inicializamos distancia_recorrida_km en 0.00 si no se proporciona.
     *
     * @param array $data
     * @return JornadaTrabajo
     * @throws ValidationException
     */
    public function create(array $data): JornadaTrabajo
    {
        // Validamos que las horas sean lógicas: inicio debe ser anterior a fin si ambas están presentes
        $validator = Validator::make($data, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'fecha_jornada' => 'required|date',
            'hora_inicio' => 'nullable|date',
            'hora_fin' => 'nullable|date|after:hora_inicio',
            'distancia_recorrida_km' => 'sometimes|numeric|min:0|max:999999.99',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Inicializamos distancia_recorrida_km en 0.00 si no se proporciona
        if (!isset($data['distancia_recorrida_km'])) {
            $data['distancia_recorrida_km'] = 0.00;
        }

        // TODO: Añadir lógica para cerrar jornadas abiertas al final del día
        return $this->jornada->create($data);
    }

    /**
     * Obtener todas las jornadas de trabajo.
     * Incluye la relación con usuario para mostrar información del chofer.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->jornada->with('usuario')->get();
    }

    public function getById(string $id): JornadaTrabajo
    {
        return $this->jornada->with('usuario')->findOrFail($id);
    }

    public function update(array $data, string $id): JornadaTrabajo
    {
        $jornada = $this->jornada->findOrFail($id);
        $validator = Validator::make($data, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'fecha_jornada' => 'required|date',
            'hora_inicio' => 'nullable|date',
            'hora_fin' => 'nullable|date|after:hora_inicio',
            'distancia_recorrida_km' => 'sometimes|numeric|min:0|max:999999.99',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (!isset($data['distancia_recorrida_km'])) {
            $data['distancia_recorrida_km'] = 0.00;
        }
        $jornada->update($data);
        return $jornada;
    }

    public function delete(string $id): bool
    {
        $jornada = $this->jornada->findOrFail($id);
        return $jornada->delete();
    }
}

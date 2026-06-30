<?php

namespace App\Services;

use App\Models\Notificacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para Notificacion - Autor: Ulises
class NotificacionService
{
    protected Notificacion $notificacion;

    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Crear una nueva notificación con validación.
     * Nota: el campo leida por defecto es 0 (no leída).
     *
     * @param array $payload
     * @return Notificacion
     * @throws ValidationException
     */
    public function create(array $payload): Notificacion
    {
        // Validamos que el usuario exista antes de crear la notificación
        $validator = Validator::make($payload, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'leida' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Nota: el campo leida por defecto es 0 si no se proporciona
        if (!isset($payload['leida'])) {
            $payload['leida'] = false;
        }

        return $this->notificacion->create($payload);
    }

    /**
     * Obtener todas las notificaciones del sistema.
     * Incluye la relación con usuario para mostrar información completa.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->notificacion->with('usuario')->get();
    }

    public function getById(string $id): Notificacion
    {
        return $this->notificacion->with('usuario')->findOrFail($id);
    }

    public function update(array $payload, string $id): Notificacion
    {
        $notificacion = $this->notificacion->findOrFail($id);
        $validator = Validator::make($payload, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'leida' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (!isset($payload['leida'])) {
            $payload['leida'] = false;
        }
        $notificacion->update($payload);
        return $notificacion;
    }

    public function delete(string $id): bool
    {
        $notificacion = $this->notificacion->findOrFail($id);
        return $notificacion->delete();
    }
}

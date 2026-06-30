<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para AuditoriaLog - Autor: Ulises
class AuditoriaLogService
{
    protected AuditoriaLog $auditoriaLog;

    public function __construct(AuditoriaLog $auditoriaLog)
    {
        $this->auditoriaLog = $auditoriaLog;
    }

    /**
     * Crear un nuevo registro de auditoría con validación.
     * Aseguramos que el campo detalle_json reciba un array válido.
     *
     * @param array $registro
     * @return AuditoriaLog
     * @throws ValidationException
     */
    public function create(array $registro): AuditoriaLog
    {
        // Validamos que detalle_json sea un array válido si se proporciona
        $validator = Validator::make($registro, [
            'usuario_id' => 'nullable|uuid|exists:users,id',
            'tabla_afectada' => 'required|string|max:255',
            'accion' => 'required|string|max:50',
            'detalle_json' => 'nullable|array',
            'fecha_evento' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Convertimos el array a JSON antes de persistir
        if (isset($registro['detalle_json']) && is_array($registro['detalle_json'])) {
            $registro['detalle_json'] = json_encode($registro['detalle_json']);
        }

        // TODO: Implementar middleware de logs automático para auditoria_logs
        return $this->auditoriaLog->create($registro);
    }

    /**
     * Obtener todos los registros de auditoría.
     * Incluye la relación con usuario para mostrar información del responsable.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->auditoriaLog->with('usuario')->get();
    }

    public function getById(string $id): AuditoriaLog
    {
        return $this->auditoriaLog->with('usuario')->findOrFail($id);
    }

    public function update(array $registro, string $id): AuditoriaLog
    {
        $auditoriaLog = $this->auditoriaLog->findOrFail($id);
        $validator = Validator::make($registro, [
            'usuario_id' => 'nullable|uuid|exists:users,id',
            'tabla_afectada' => 'required|string|max:255',
            'accion' => 'required|string|max:50',
            'detalle_json' => 'nullable|array',
            'fecha_evento' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (isset($registro['detalle_json']) && is_array($registro['detalle_json'])) {
            $registro['detalle_json'] = json_encode($registro['detalle_json']);
        }
        $auditoriaLog->update($registro);
        return $auditoriaLog;
    }

    public function delete(string $id): bool
    {
        $auditoriaLog = $this->auditoriaLog->findOrFail($id);
        return $auditoriaLog->delete();
    }
}

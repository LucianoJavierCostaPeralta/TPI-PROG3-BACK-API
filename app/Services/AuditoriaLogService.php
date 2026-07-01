<?php

namespace App\Services;

use App\Models\AuditoriaLog;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLogService
{
    public function record(
        Empresa|string $empresa,
        ?User $actor,
        Model|string|null $recurso,
        string $tabla,
        string $accion,
        array $detalle = [],
    ): AuditoriaLog {
        return AuditoriaLog::create([
            'empresa_id' => $empresa instanceof Empresa ? $empresa->getKey() : $empresa,
            'usuario_id' => $actor?->getKey(),
            'tabla_afectada' => $tabla,
            'recurso_id' => $recurso instanceof Model ? $recurso->getKey() : $recurso,
            'accion' => $accion,
            'detalle_json' => $detalle ?: null,
            'fecha_evento' => now(),
        ]);
    }
}

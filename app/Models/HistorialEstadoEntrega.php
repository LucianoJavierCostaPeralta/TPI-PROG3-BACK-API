<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoEntrega extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'historial_estados_entrega';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'entrega_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'motivo_rechazo_id',
        'usuario_id',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class);
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoEntrega::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoEntrega::class, 'estado_nuevo_id');
    }

    public function motivoRechazo(): BelongsTo
    {
        return $this->belongsTo(MotivoRechazo::class, 'motivo_rechazo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

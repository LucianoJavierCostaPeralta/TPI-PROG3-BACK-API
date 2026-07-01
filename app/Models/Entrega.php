<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Entrega extends Model
{
    use HasFactory, HasUuids;

    public const ESTADO_PENDING = 1;

    public const ESTADO_ASSIGNED = 2;

    public const ESTADO_ACCEPTED = 3;

    public const ESTADO_ON_THE_WAY = 4;

    public const ESTADO_DELIVERED = 5;

    public const ESTADO_FINISHED = 6;

    public const ESTADO_CANCELLED = 7;

    public const DRIVER_ESTADOS = [
        self::ESTADO_ON_THE_WAY,
        self::ESTADO_DELIVERED,
        self::ESTADO_FINISHED,
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'chofer_id',
        'cliente_id',
        'cliente',
        'producto',
        'estado_id',
        'direccion_destino',
        'orden_ruta',
        'referencia',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Entrega $entrega): void {
            if (empty($entrega->id)) {
                $entrega->id = (string) Str::uuid();
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function chofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chofer_id');
    }

    public function legacyCliente(): BelongsTo
    {
        return $this->belongsTo(ClienteDestinatario::class, 'cliente_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoEntrega::class, 'estado_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleEntrega::class);
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(ComprobanteEntrega::class);
    }

    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoEntrega::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrega extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'chofer_id',
        'cliente_id',
        'estado_id',
        'direccion_destino',
        'latitud',
        'longitud',
        'orden_ruta',
        'referencia',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function chofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chofer_id');
    }

    public function cliente(): BelongsTo
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

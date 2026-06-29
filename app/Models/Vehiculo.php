<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Vehiculo extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'tipo_id',
        'patente',
        'marca_modelo',
        'estado_operativo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionVehiculo::class);
    }
}

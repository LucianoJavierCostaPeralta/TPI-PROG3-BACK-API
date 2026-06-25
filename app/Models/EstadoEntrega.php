<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoEntrega extends Model
{
    use HasFactory;

    protected $table = 'estados_entrega';

    protected $fillable = [
        'nombre_estado',
    ];

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'estado_id');
    }

    public function historialesComoAnterior(): HasMany
    {
        return $this->hasMany(HistorialEstadoEntrega::class, 'estado_anterior_id');
    }

    public function historialesComoNuevo(): HasMany
    {
        return $this->hasMany(HistorialEstadoEntrega::class, 'estado_nuevo_id');
    }
}

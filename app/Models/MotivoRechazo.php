<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoRechazo extends Model
{
    use HasFactory;

    protected $table = 'motivos_rechazo';

    protected $fillable = [
        'descripcion',
    ];

    public function historiales(): HasMany
    {
        return $this->hasMany(HistorialEstadoEntrega::class, 'motivo_rechazo_id');
    }
}

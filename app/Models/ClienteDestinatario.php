<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteDestinatario extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'nombre_completo',
        'telefono',
        'direccion_frecuente',
        'latitud_frecuente',
        'longitud_frecuente',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'cliente_id');
    }
}

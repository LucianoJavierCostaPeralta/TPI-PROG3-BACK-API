<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAsesoramiento extends Model
{
    use HasFactory, HasUuids;

    // La tabla existente no sigue la pluralización convencional de Eloquent.
    protected $table = 'solicitudes_asesoramiento';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'usuario_id',
        'datos_usuario',
        'mensaje',
        'nombre_empresa',
        'cuit',
        'correo_corporativo',
        'telefono',
        'cantidad_vehiculos',
        'leido',
    ];

    protected function casts(): array
    {
        return [
            'datos_usuario' => 'array',
            'leido' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

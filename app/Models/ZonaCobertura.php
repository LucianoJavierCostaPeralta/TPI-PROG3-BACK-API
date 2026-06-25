<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ZonaCobertura extends Model
{
    use HasFactory;

    protected $table = 'zonas_cobertura';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'nombre_zona',
        'codigo_postal',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function choferes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chofer_zonas', 'zona_id', 'usuario_id');
    }
}

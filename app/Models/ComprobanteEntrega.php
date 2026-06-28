<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComprobanteEntrega extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'entrega_id',
        'url_foto',
        'latitud_captura',
        'longitud_captura',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class);
    }
}

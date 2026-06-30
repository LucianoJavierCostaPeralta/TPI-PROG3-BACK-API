<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SolicitudAsesoramiento extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nombre_empresa',
        'cuit',
        'correo_corporativo',
        'telefono',
        'cantidad_vehiculos',
        'leido',
    ];
}

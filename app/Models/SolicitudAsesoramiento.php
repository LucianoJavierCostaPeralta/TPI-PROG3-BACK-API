<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudAsesoramiento extends Model
{
    use HasFactory;

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

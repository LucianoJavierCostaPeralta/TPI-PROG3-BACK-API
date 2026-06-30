<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'razon_social',
        'cuit',
        'email_contacto',
        'telefono',
        'tamano_flota',
        'terminos_aceptados_en',
    ];

    protected function casts(): array
    {
        return [
            'terminos_aceptados_en' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(ClienteDestinatario::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class);
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function zonas(): HasMany
    {
        return $this->hasMany(ZonaCobertura::class);
    }
}

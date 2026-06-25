<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/** @use HasFactory<UserFactory> */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'rol_id',
        'nombre_completo',
        'email',
        'password',
        'telefono',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'chofer_id');
    }

    public function asignacionesVehiculo(): HasMany
    {
        return $this->hasMany(AsignacionVehiculo::class, 'usuario_id');
    }

    public function jornadasTrabajo(): HasMany
    {
        return $this->hasMany(JornadaTrabajo::class, 'usuario_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoEntrega::class, 'usuario_id');
    }

    public function zonasCobertura(): BelongsToMany
    {
        return $this->belongsToMany(ZonaCobertura::class, 'chofer_zonas', 'usuario_id', 'zona_id');
    }
}

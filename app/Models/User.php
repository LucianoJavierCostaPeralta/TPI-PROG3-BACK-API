<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/** @use HasFactory<UserFactory> */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    public const ROL_ADMIN = 1;

    public const ROL_CHOFER = 2;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'empresa_id',
        'rol_id',
        'nombre_completo',
        'dni',
        'fecha_nacimiento',
        'email',
        'password',
        'telefono',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'fecha_nacimiento' => 'date',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->rol_id === self::ROL_ADMIN;
    }

    public function isChofer(): bool
    {
        return $this->rol_id === self::ROL_CHOFER;
    }

    public function hasRole(string $role): bool
    {
        $this->loadMissing('rol');

        return strtolower($this->rol?->nombre_rol ?? '') === strtolower($role);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->id)) {
                $user->id = (string) Str::uuid();
            }
        });
    }

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

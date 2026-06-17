<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chofer_id', 'direccion_origen', 'direccion_destino', 'descripcion', 'state'])]
class Envio extends Model
{
    use HasFactory;

    protected $table = 'envios';

    public const STATE_PENDING = 'pending';

    public const STATE_ASSIGNED = 'assigned';

    public const STATE_ACCEPTED = 'accepted';

    public const STATE_ON_THE_WAY = 'on_the_way';

    public const STATE_DELIVERED = 'delivered';

    public const STATE_FINISHED = 'finished';

    public const STATE_CANCELLED = 'cancelled';

    public const STATES = [
        self::STATE_PENDING,
        self::STATE_ASSIGNED,
        self::STATE_ACCEPTED,
        self::STATE_ON_THE_WAY,
        self::STATE_DELIVERED,
        self::STATE_FINISHED,
        self::STATE_CANCELLED,
    ];

    public const DRIVER_STATES = [
        self::STATE_ON_THE_WAY,
        self::STATE_DELIVERED,
        self::STATE_FINISHED,
    ];

    public function chofer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chofer_id');
    }
}

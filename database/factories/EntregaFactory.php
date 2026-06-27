<?php

namespace Database\Factories;

use App\Models\ClienteDestinatario;
use App\Models\Empresa;
use App\Models\Entrega;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Entrega>
 */
class EntregaFactory extends Factory
{
    protected $model = Entrega::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'empresa_id' => Empresa::factory(),
            'chofer_id' => null,
            'cliente_id' => ClienteDestinatario::factory(),
            'estado_id' => Entrega::ESTADO_PENDING,
            'direccion_destino' => fake()->streetAddress(),
            'latitud' => fake()->latitude(),
            'longitud' => fake()->longitude(),
            'orden_ruta' => fake()->numberBetween(1, 10),
            'referencia' => fake()->optional()->sentence(),
            'fecha_asignacion' => null,
        ];
    }

    public function assignedTo(User $chofer): static
    {
        return $this->state(fn (array $attributes) => [
            'chofer_id' => $chofer->id,
            'empresa_id' => $chofer->empresa_id,
            'estado_id' => Entrega::ESTADO_ASSIGNED,
            'fecha_asignacion' => now(),
        ]);
    }

    public function withEstado(int $estadoId): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_id' => $estadoId,
        ]);
    }
}

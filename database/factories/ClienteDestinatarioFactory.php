<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\ClienteDestinatario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClienteDestinatario>
 */
class ClienteDestinatarioFactory extends Factory
{
    protected $model = ClienteDestinatario::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'empresa_id' => Empresa::factory(),
            'nombre_completo' => fake()->name(),
            'telefono' => fake()->numerify('##########'),
            'direccion_frecuente' => fake()->streetAddress(),
            'latitud_frecuente' => fake()->latitude(),
            'longitud_frecuente' => fake()->longitude(),
        ];
    }
}

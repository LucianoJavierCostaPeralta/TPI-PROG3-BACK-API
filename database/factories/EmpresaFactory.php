<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'razon_social' => fake()->company(),
            'cuit' => fake()->unique()->numerify('##-########-#'),
            'email_contacto' => fake()->companyEmail(),
            'telefono' => fake()->numerify('##########'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'empresa_id' => Empresa::factory(),
            'rol_id' => User::ROL_CHOFER,
            'nombre_completo' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= 'password',
            'telefono' => fake()->numerify('##########'),
            'activo' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol_id' => User::ROL_ADMIN,
        ]);
    }

    public function chofer(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol_id' => User::ROL_CHOFER,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}

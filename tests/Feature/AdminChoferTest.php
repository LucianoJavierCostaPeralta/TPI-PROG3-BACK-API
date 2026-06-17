<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminChoferTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_chofer(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson('/api/admin/choferes', [
            'name' => 'Juan',
            'apellido' => 'Perez',
            'dni' => '12345678',
            'fecha_nacimiento' => '1990-05-10',
            'licencia' => 'B123456',
            'email' => 'juan@example.com',
            'telefono' => '3515551234',
            'password' => '123456',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'juan@example.com')
            ->assertJsonPath('data.dni', '12345678')
            ->assertJsonPath('data.role', 'chofer')
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'dni' => '12345678',
            'role' => 'chofer',
        ]);
    }

    public function test_admin_cannot_create_chofer_with_invalid_dni(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->postJson('/api/admin/choferes', [
            'name' => 'Juan',
            'apellido' => 'Perez',
            'dni' => '12A4567*',
            'fecha_nacimiento' => '1990-05-10',
            'licencia' => 'B123456',
            'email' => 'juan@example.com',
            'telefono' => '3515551234',
            'password' => '123456',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dni');
    }

    public function test_chofer_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'chofer']));

        $this->getJson('/api/admin/choferes')
            ->assertForbidden()
            ->assertJsonPath('message', 'No tenes permisos para realizar esta accion.');
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/admin/choferes')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'No autenticado.');
    }

    public function test_admin_can_reset_chofer_password(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $chofer = User::factory()->create([
            'role' => 'chofer',
            'password' => Hash::make('password-vieja'),
        ]);

        $this->patchJson("/api/admin/choferes/{$chofer->id}/password", [
            'password' => 'password-nueva',
        ])->assertOk();

        $this->assertTrue(Hash::check('password-nueva', $chofer->fresh()->password));
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminChoferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_create_chofer(): void
    {
        Sanctum::actingAs(User::where('email', 'admin@admin.com')->first());

        $response = $this->postJson('/api/v1/admin/choferes', [
            'nombre_completo' => 'Juan Perez',
            'email' => 'juan@example.com',
            'telefono' => '3515551234',
            'password' => '123456',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'juan@example.com')
            ->assertJsonPath('data.rol_id', User::ROL_CHOFER)
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'rol_id' => User::ROL_CHOFER,
        ]);
    }

    public function test_admin_cannot_create_chofer_with_invalid_nombre(): void
    {
        Sanctum::actingAs(User::where('email', 'admin@admin.com')->first());

        $this->postJson('/api/v1/admin/choferes', [
            'nombre_completo' => 'Juan123',
            'email' => 'juan@example.com',
            'telefono' => '3515551234',
            'password' => '123456',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre_completo');
    }

    public function test_chofer_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs(User::where('email', 'chofer@logistica.com')->first());

        $this->getJson('/api/v1/admin/choferes')
            ->assertForbidden()
            ->assertJsonPath('message', 'No tenes permisos para realizar esta accion.');
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/v1/admin/choferes')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'No autenticado.');
    }

    public function test_admin_can_reset_chofer_password(): void
    {
        Sanctum::actingAs(User::where('email', 'admin@admin.com')->first());
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        $chofer->update(['password' => 'password-vieja']);

        $this->patchJson("/api/v1/admin/choferes/{$chofer->id}/password", [
            'password' => 'password-nueva',
        ])->assertOk();

        $this->assertTrue(Hash::check('password-nueva', $chofer->fresh()->password));
    }
}

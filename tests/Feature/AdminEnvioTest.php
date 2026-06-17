<?php

namespace Tests\Feature;

use App\Models\Envio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEnvioTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_envio(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson('/api/admin/envios', [
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'descripcion' => 'Paquetes medianos',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.direccion_origen', 'Calle 123')
            ->assertJsonPath('data.direccion_destino', 'Calle 321')
            ->assertJsonPath('data.state', Envio::STATE_PENDING);

        $this->assertDatabaseHas('envios', [
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_PENDING,
        ]);
    }

    public function test_admin_can_list_envios(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        Envio::create([
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_PENDING,
        ]);

        $this->getJson('/api/admin/envios')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.state', Envio::STATE_PENDING);
    }

    public function test_admin_can_assign_envio_to_chofer(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $chofer = User::factory()->create(['role' => 'chofer']);
        $envio = Envio::create([
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_PENDING,
        ]);

        $this->patchJson("/api/admin/envios/{$envio->id}/assign", [
            'chofer_id' => $chofer->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.chofer_id', $chofer->id)
            ->assertJsonPath('data.state', Envio::STATE_ASSIGNED);

        $this->assertDatabaseHas('envios', [
            'id' => $envio->id,
            'chofer_id' => $chofer->id,
            'state' => Envio::STATE_ASSIGNED,
        ]);
    }

    public function test_admin_cannot_assign_envio_already_accepted(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $chofer = User::factory()->create(['role' => 'chofer']);
        $envio = Envio::create([
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_ACCEPTED,
        ]);

        $this->patchJson("/api/admin/envios/{$envio->id}/assign", [
            'chofer_id' => $chofer->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Solo se pueden asignar envios pendientes o asignados.');
    }

    public function test_admin_cannot_assign_envio_to_admin_user(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $admin = User::factory()->create(['role' => 'admin']);
        $envio = Envio::create([
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_PENDING,
        ]);

        $this->patchJson("/api/admin/envios/{$envio->id}/assign", [
            'chofer_id' => $admin->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('chofer_id');
    }
}

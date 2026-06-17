<?php

namespace Tests\Feature;

use App\Models\Envio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChoferEnvioTest extends TestCase
{
    use RefreshDatabase;

    public function test_chofer_can_list_assigned_envios(): void
    {
        $chofer = User::factory()->create(['role' => 'chofer']);
        Sanctum::actingAs($chofer);

        Envio::create([
            'chofer_id' => $chofer->id,
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_ASSIGNED,
        ]);

        $this->getJson('/api/chofer/envios')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.state', Envio::STATE_ASSIGNED);
    }

    public function test_chofer_can_accept_assigned_envio(): void
    {
        $chofer = User::factory()->create(['role' => 'chofer']);
        Sanctum::actingAs($chofer);
        $envio = Envio::create([
            'chofer_id' => $chofer->id,
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_ASSIGNED,
        ]);

        $this->patchJson("/api/chofer/envios/{$envio->id}/accept")
            ->assertOk()
            ->assertJsonPath('data.state', Envio::STATE_ACCEPTED);
    }

    public function test_chofer_can_move_envio_to_en_camino_and_entregado(): void
    {
        $chofer = User::factory()->create(['role' => 'chofer']);
        Sanctum::actingAs($chofer);
        $envio = Envio::create([
            'chofer_id' => $chofer->id,
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_ACCEPTED,
        ]);

        $this->patchJson("/api/chofer/envios/{$envio->id}/state", [
            'state' => Envio::STATE_ON_THE_WAY,
        ])
            ->assertOk()
            ->assertJsonPath('data.state', Envio::STATE_ON_THE_WAY);

        $this->patchJson("/api/chofer/envios/{$envio->id}/state", [
            'state' => Envio::STATE_DELIVERED,
        ])
            ->assertOk()
            ->assertJsonPath('data.state', Envio::STATE_DELIVERED);
    }

    public function test_chofer_cannot_update_envio_from_another_chofer(): void
    {
        $chofer = User::factory()->create(['role' => 'chofer']);
        $otherChofer = User::factory()->create(['role' => 'chofer']);
        Sanctum::actingAs($chofer);
        $envio = Envio::create([
            'chofer_id' => $otherChofer->id,
            'direccion_origen' => 'Calle 123',
            'direccion_destino' => 'Calle 321',
            'state' => Envio::STATE_ASSIGNED,
        ]);

        $this->patchJson("/api/chofer/envios/{$envio->id}/accept")
            ->assertNotFound();
    }
}

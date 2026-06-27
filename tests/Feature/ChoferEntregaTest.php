<?php

namespace Tests\Feature;

use App\Models\ClienteDestinatario;
use App\Models\Entrega;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChoferEntregaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_chofer_can_list_assigned_entregas(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($chofer);

        Entrega::factory()->assignedTo($chofer)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->getJson('/api/chofer/entregas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estado_id', Entrega::ESTADO_ASSIGNED);
    }

    public function test_chofer_can_show_assigned_entrega(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($chofer);

        $entrega = Entrega::factory()->assignedTo($chofer)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->getJson("/api/chofer/entregas/{$entrega->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $entrega->id);
    }

    public function test_chofer_can_accept_assigned_entrega(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($chofer);

        $entrega = Entrega::factory()->assignedTo($chofer)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->patchJson("/api/chofer/entregas/{$entrega->id}/accept")
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ACCEPTED);
    }

    public function test_chofer_can_move_entrega_to_en_camino_and_entregado(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($chofer);

        $entrega = Entrega::factory()->assignedTo($chofer)->withEstado(Entrega::ESTADO_ACCEPTED)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->patchJson("/api/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_ON_THE_WAY,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ON_THE_WAY);

        $this->patchJson("/api/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_DELIVERED);
    }

    public function test_chofer_cannot_update_entrega_from_another_chofer(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        $otherChofer = User::factory()->chofer()->create([
            'empresa_id' => $chofer->empresa_id,
            'email' => 'otro-chofer@example.com',
        ]);

        Sanctum::actingAs($chofer);

        $entrega = Entrega::factory()->assignedTo($otherChofer)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->patchJson("/api/chofer/entregas/{$entrega->id}/accept")
            ->assertNotFound();
    }
}

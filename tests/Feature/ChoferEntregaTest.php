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

        $this->getJson('/api/v1/chofer/entregas')
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

        $this->getJson("/api/v1/chofer/entregas/{$entrega->id}")
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

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/accept")
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ACCEPTED);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_ASSIGNED,
            'estado_nuevo_id' => Entrega::ESTADO_ACCEPTED,
            'usuario_id' => $chofer->id,
        ]);
    }

    public function test_chofer_can_complete_entrega_cycle(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($chofer);

        $entrega = Entrega::factory()->assignedTo($chofer)->withEstado(Entrega::ESTADO_ACCEPTED)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $chofer->empresa_id,
            ])->id,
        ]);

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_ON_THE_WAY,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ON_THE_WAY);

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_DELIVERED);
        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_FINISHED,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_FINISHED);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_ACCEPTED,
            'estado_nuevo_id' => Entrega::ESTADO_ON_THE_WAY,
            'usuario_id' => $chofer->id,
        ]);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_ON_THE_WAY,
            'estado_nuevo_id' => Entrega::ESTADO_DELIVERED,
            'usuario_id' => $chofer->id,
        ]);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_DELIVERED,
            'estado_nuevo_id' => Entrega::ESTADO_FINISHED,
            'usuario_id' => $chofer->id,
        ]);
        $this->assertDatabaseCount('historial_estados_entrega', 3);
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

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/accept")
            ->assertNotFound();
    }
}

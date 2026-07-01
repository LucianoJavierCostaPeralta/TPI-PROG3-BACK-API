<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClienteDniEntregaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_must_provide_cliente_dni_when_creating_entrega(): void
    {
        Sanctum::actingAs(User::where('email', 'admin@admin.com')->firstOrFail());

        $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Comercio Centro',
            'producto' => 'Caja mediana',
            'direccion_destino' => 'Calle 321',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cliente_dni');
    }

    public function test_chofer_cannot_see_expected_cliente_dni(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $entrega = Entrega::factory()->assignedTo($chofer)->create([
            'cliente_dni' => '30123456',
        ]);
        Sanctum::actingAs($chofer);

        $this->getJson("/api/v1/chofer/entregas/{$entrega->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.cliente_dni');
    }

    public function test_chofer_cannot_deliver_without_matching_cliente_dni(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $entrega = Entrega::factory()
            ->assignedTo($chofer)
            ->withEstado(Entrega::ESTADO_ON_THE_WAY)
            ->create(['cliente_dni' => '30123456']);
        Sanctum::actingAs($chofer);

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cliente_dni');

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED,
            'cliente_dni' => '30999999',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cliente_dni');

        $this->assertSame(Entrega::ESTADO_ON_THE_WAY, $entrega->fresh()->estado_id);
        $this->assertDatabaseCount('historial_estados_entrega', 0);
    }

    public function test_chofer_can_deliver_with_matching_cliente_dni(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $entrega = Entrega::factory()
            ->assignedTo($chofer)
            ->withEstado(Entrega::ESTADO_ON_THE_WAY)
            ->create(['cliente_dni' => '30123456']);
        Sanctum::actingAs($chofer);

        $this->patchJson("/api/v1/chofer/entregas/{$entrega->id}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED,
            'cliente_dni' => '30123456',
        ])
            ->assertOk()
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_DELIVERED)
            ->assertJsonMissingPath('data.cliente_dni');

        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_ON_THE_WAY,
            'estado_nuevo_id' => Entrega::ESTADO_DELIVERED,
            'usuario_id' => $chofer->id,
        ]);
    }
}

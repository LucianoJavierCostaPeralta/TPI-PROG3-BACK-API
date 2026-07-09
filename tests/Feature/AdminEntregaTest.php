<?php

namespace Tests\Feature;

use App\Models\ClienteDestinatario;
use App\Models\Entrega;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEntregaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_create_entrega(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Comercio Centro',
            'cliente_dni' => '30123456',
            'producto' => 'Caja mediana',
            'direccion_destino' => 'Calle 321',
            'referencia' => 'Paquetes medianos',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.cliente', 'Comercio Centro')
            ->assertJsonPath('data.cliente_dni', '30123456')
            ->assertJsonPath('data.producto', 'Caja mediana')
            ->assertJsonPath('data.direccion_destino', 'Calle 321')
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_PENDING);

        $this->assertDatabaseHas('entregas', [
            'cliente' => 'Comercio Centro',
            'cliente_dni' => '30123456',
            'producto' => 'Caja mediana',
            'direccion_destino' => 'Calle 321',
            'estado_id' => Entrega::ESTADO_PENDING,
        ]);
    }

    public function test_admin_can_list_entregas(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
            'estado_id' => Entrega::ESTADO_PENDING,
        ]);

        $this->getJson('/api/v1/admin/entregas')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.estado_id', Entrega::ESTADO_PENDING);
    }

    public function test_admin_can_show_entrega(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
        ]);

        $this->getJson("/api/v1/admin/entregas/{$entrega->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $entrega->id);
    }

    public function test_admin_can_assign_entrega_to_chofer(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
            'estado_id' => Entrega::ESTADO_PENDING,
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}/assign", [
            'chofer_id' => $chofer->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.chofer_id', $chofer->id)
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ASSIGNED);

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'chofer_id' => $chofer->id,
            'estado_id' => Entrega::ESTADO_ASSIGNED,
        ]);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_PENDING,
            'estado_nuevo_id' => Entrega::ESTADO_ASSIGNED,
            'usuario_id' => $admin->id,
        ]);
    }

    public function test_admin_can_reassign_entrega_to_another_chofer(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $choferActual = User::where('email', 'chofer@logistica.com')->first();
        $nuevoChofer = User::factory()->chofer()->create([
            'empresa_id' => $admin->empresa_id,
        ]);
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->assignedTo($choferActual)->create([
            'empresa_id' => $admin->empresa_id,
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}/assign", [
            'chofer_id' => $nuevoChofer->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.chofer_id', $nuevoChofer->id)
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_ASSIGNED);

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'chofer_id' => $nuevoChofer->id,
            'estado_id' => Entrega::ESTADO_ASSIGNED,
        ]);
        $this->assertDatabaseCount('historial_estados_entrega', 0);
    }

    public function test_admin_can_unassign_entrega(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->assignedTo($chofer)->create([
            'empresa_id' => $admin->empresa_id,
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}/assign", [
            'chofer_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Entrega desasignada correctamente.')
            ->assertJsonPath('data.chofer_id', null)
            ->assertJsonPath('data.estado_id', Entrega::ESTADO_PENDING);

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'chofer_id' => null,
            'estado_id' => Entrega::ESTADO_PENDING,
            'fecha_asignacion' => null,
        ]);
        $this->assertDatabaseHas('historial_estados_entrega', [
            'entrega_id' => $entrega->id,
            'estado_anterior_id' => Entrega::ESTADO_ASSIGNED,
            'estado_nuevo_id' => Entrega::ESTADO_PENDING,
            'usuario_id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_assign_entrega_already_accepted(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
            'chofer_id' => $chofer->id,
            'estado_id' => Entrega::ESTADO_ACCEPTED,
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}/assign", [
            'chofer_id' => $chofer->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Solo se pueden asignar o desasignar entregas pendientes o asignadas.');
    }

    public function test_admin_cannot_assign_entrega_to_admin_user(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
            'estado_id' => Entrega::ESTADO_PENDING,
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}/assign", [
            'chofer_id' => $admin->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('chofer_id');
    }

    public function test_admin_can_update_entrega(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->assignedTo($chofer)->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
            'cliente' => 'Cliente anterior',
            'producto' => 'Producto anterior',
            'direccion_destino' => 'Calle anterior 123',
            'referencia' => 'Referencia anterior',
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}", [
            'cliente' => 'Cliente editado',
            'cliente_dni' => '32123456',
            'producto' => 'Producto editado',
            'direccion_destino' => 'Calle nueva 456',
            'referencia' => 'Referencia editada',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Entrega actualizada correctamente.')
            ->assertJsonPath('data.cliente', 'Cliente editado')
            ->assertJsonPath('data.cliente_dni', '32123456')
            ->assertJsonPath('data.producto', 'Producto editado')
            ->assertJsonPath('data.direccion_destino', 'Calle nueva 456')
            ->assertJsonPath('data.referencia', 'Referencia editada');

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'cliente' => 'Cliente editado',
            'cliente_dni' => '32123456',
            'producto' => 'Producto editado',
            'direccion_destino' => 'Calle nueva 456',
            'referencia' => 'Referencia editada',
        ]);
        $this->assertDatabaseHas('auditoria_logs', [
            'recurso_id' => $entrega->id,
            'accion' => 'entrega.updated',
            'tabla_afectada' => 'entregas',
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $chofer->id,
            'titulo' => 'Entrega actualizada',
            'tipo' => 'info',
            'leida' => false,
        ]);
    }

    public function test_admin_cannot_update_entrega_from_another_empresa(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'cliente' => 'Cliente original',
        ]);

        $this->patchJson("/api/v1/admin/entregas/{$entrega->id}", [
            'cliente' => 'Cliente editado',
        ])
            ->assertNotFound();

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'cliente' => 'Cliente original',
        ]);
    }

    public function test_admin_can_delete_entrega(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create([
            'empresa_id' => $admin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $admin->empresa_id,
            ])->id,
        ]);

        $this->deleteJson("/api/v1/admin/entregas/{$entrega->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Entrega eliminada correctamente.');

        $this->assertDatabaseMissing('entregas', [
            'id' => $entrega->id,
        ]);
        $this->assertDatabaseHas('auditoria_logs', [
            'recurso_id' => $entrega->id,
            'accion' => 'entrega.deleted',
            'tabla_afectada' => 'entregas',
        ]);
    }

    public function test_admin_cannot_delete_entrega_from_another_empresa(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        Sanctum::actingAs($admin);

        $entrega = Entrega::factory()->create();

        $this->deleteJson("/api/v1/admin/entregas/{$entrega->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
        ]);
    }
}

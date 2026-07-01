<?php

namespace Tests\Feature;

use App\Models\AuditoriaLog;
use App\Models\Empresa;
use App\Models\Entrega;
use App\Models\User;
use App\Services\AuditoriaLogService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_chofer_operations_create_safe_audit_events(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $created = $this->postJson('/api/v1/admin/choferes', [
            'nombre_completo' => 'Juan Perez', 'dni' => '87654321',
            'fecha_nacimiento' => '1990-05-12', 'email' => 'audit@example.com',
            'password' => 'secreto123',
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/admin/choferes/{$created}", ['dni' => '11223344'])->assertOk();
        $this->patchJson("/api/v1/admin/choferes/{$created}/password", ['password' => 'nuevo-secreto'])->assertOk();
        $this->deleteJson("/api/v1/admin/choferes/{$created}")->assertOk();

        $this->assertSame(
            ['chofer.created', 'chofer.updated', 'chofer.password_reset', 'chofer.deleted'],
            AuditoriaLog::oldest('fecha_evento')->pluck('accion')->all(),
        );
        $serialized = AuditoriaLog::all()->toJson();
        $this->assertStringNotContainsString('87654321', $serialized);
        $this->assertStringNotContainsString('11223344', $serialized);
        $this->assertStringNotContainsString('secreto123', $serialized);
        $this->assertStringNotContainsString('nuevo-secreto', $serialized);
    }

    public function test_entrega_operations_create_all_audit_events_without_cliente_dni(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $otro = User::factory()->chofer()->create(['empresa_id' => $admin->empresa_id]);
        Sanctum::actingAs($admin);

        $entregaId = $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Cliente Audit', 'cliente_dni' => '30123456', 'producto' => 'Caja',
            'direccion_destino' => 'Calle Siempre Viva 123',
        ])->assertCreated()->json('data.id');
        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", ['chofer_id' => $chofer->id])->assertOk();
        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", ['chofer_id' => $otro->id])->assertOk();
        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", ['chofer_id' => null])->assertOk();
        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", ['chofer_id' => $chofer->id])->assertOk();

        Sanctum::actingAs($chofer);
        $this->patchJson("/api/v1/chofer/entregas/{$entregaId}/accept")->assertOk();
        $this->patchJson("/api/v1/chofer/entregas/{$entregaId}/state", ['estado_id' => Entrega::ESTADO_ON_THE_WAY])->assertOk();
        $this->patchJson("/api/v1/chofer/entregas/{$entregaId}/state", [
            'estado_id' => Entrega::ESTADO_DELIVERED, 'cliente_dni' => '30123456',
        ])->assertOk();

        foreach (['entrega.created', 'entrega.assigned', 'entrega.reassigned', 'entrega.unassigned',
            'entrega.accepted', 'entrega.on_the_way', 'entrega.delivered'] as $accion) {
            $this->assertDatabaseHas('auditoria_logs', ['recurso_id' => $entregaId, 'accion' => $accion]);
        }
        $this->assertStringNotContainsString('30123456', AuditoriaLog::all()->toJson());
    }

    public function test_audit_read_endpoints_are_filtered_paginated_and_isolated(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $otraEmpresa = Empresa::factory()->create();
        $otroAdmin = User::factory()->admin()->create(['empresa_id' => $otraEmpresa->id]);
        $propio = $this->log($admin, 'chofer.created', 'choferes');
        $this->log($admin, 'entrega.created', 'entregas');
        $ajeno = $this->log($otroAdmin, 'chofer.created', 'choferes');

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/auditoria?accion=chofer.created&recurso=choferes&usuario_id='.$admin->id.'&per_page=1')
            ->assertOk()->assertJsonCount(1, 'data.data')->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $propio->id)
            ->assertJsonPath('data.data.0.usuario.email', $admin->email)
            ->assertJsonMissingPath('data.data.0.usuario.dni');
        $this->getJson("/api/v1/admin/auditoria/{$ajeno->id}")->assertNotFound();

        Sanctum::actingAs(User::where('email', 'chofer@logistica.com')->firstOrFail());
        $this->getJson('/api/v1/admin/auditoria')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/admin/auditoria')->assertUnauthorized();
    }

    public function test_business_operation_rolls_back_when_audit_write_fails(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        Sanctum::actingAs($admin);
        $this->mock(AuditoriaLogService::class)
            ->shouldReceive('record')->once()->andThrow(new RuntimeException('audit failed'));

        $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Rollback', 'cliente_dni' => '30123456', 'producto' => 'Caja',
            'direccion_destino' => 'Calle Rollback 123',
        ])->assertServerError();

        $this->assertDatabaseMissing('entregas', ['cliente' => 'Rollback']);
    }

    private function log(User $actor, string $accion, string $recurso): AuditoriaLog
    {
        return AuditoriaLog::create([
            'empresa_id' => $actor->empresa_id, 'usuario_id' => $actor->id,
            'tabla_afectada' => $recurso, 'recurso_id' => $actor->id,
            'accion' => $accion, 'detalle_json' => ['safe' => true], 'fecha_evento' => now(),
        ]);
    }
}

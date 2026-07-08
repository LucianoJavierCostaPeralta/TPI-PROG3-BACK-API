<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\Notificacion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_list_and_mark_own_notifications(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($admin);

        Notificacion::create([
            'usuario_id' => $admin->id,
            'titulo' => 'Primera notificación',
            'mensaje' => 'Mensaje para el admin',
            'tipo' => 'info',
            'leida' => false,
        ]);
        Notificacion::create([
            'usuario_id' => $admin->id,
            'titulo' => 'Segunda notificación',
            'mensaje' => 'Otro mensaje para el admin',
            'tipo' => 'success',
            'leida' => false,
        ]);
        Notificacion::create([
            'usuario_id' => $chofer->id,
            'titulo' => 'No visible',
            'mensaje' => 'No debería verse para el admin',
            'tipo' => 'warning',
            'leida' => false,
        ]);

        $response = $this->getJson('/api/v1/notificaciones')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $notificationId = $response->json('data.0.id');

        $this->patchJson("/api/v1/notificaciones/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.leida', true);

        $this->patchJson('/api/v1/notificaciones/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated', 1);

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $admin->id,
            'titulo' => 'Primera notificación',
            'leida' => true,
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $admin->id,
            'titulo' => 'Segunda notificación',
            'leida' => true,
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $chofer->id,
            'titulo' => 'No visible',
            'leida' => false,
        ]);
    }

    public function test_delivery_assignment_creates_notification_for_driver(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $entregaResponse = $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Comercio Centro',
            'cliente_dni' => '30123456',
            'producto' => 'Caja de documentos',
            'direccion_destino' => 'Av. Colon 1234',
            'referencia' => 'Entregar por recepcion',
        ])->assertCreated();

        $entregaId = $entregaResponse->json('data.id');

        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", [
            'chofer_id' => $chofer->id,
        ])->assertOk();

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $chofer->id,
            'titulo' => 'Nueva entrega asignada',
            'leida' => false,
        ]);
    }

    public function test_driver_acceptance_notifies_company_admins(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $entregaResponse = $this->postJson('/api/v1/admin/entregas', [
            'cliente' => 'Comercio Centro',
            'cliente_dni' => '30123456',
            'producto' => 'Caja de documentos',
            'direccion_destino' => 'Av. Colon 1234',
            'referencia' => 'Entregar por recepcion',
        ])->assertCreated();

        $entregaId = $entregaResponse->json('data.id');

        $this->patchJson("/api/v1/admin/entregas/{$entregaId}/assign", [
            'chofer_id' => $chofer->id,
        ])->assertOk();

        Sanctum::actingAs($chofer);
        $this->patchJson("/api/v1/chofer/entregas/{$entregaId}/accept")
            ->assertOk();

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $admin->id,
            'titulo' => 'Entrega aceptada',
            'leida' => false,
        ]);
    }

    public function test_advisory_request_notifies_company_admins(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        Sanctum::actingAs($chofer);

        $this->postJson('/api/v1/solicitudes-asesoramiento', [
            'mensaje' => 'Necesito ayuda con las zonas de reparto.',
        ])->assertCreated();

        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $admin->id,
            'titulo' => 'Nueva solicitud de asesoramiento',
            'leida' => false,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SolicitudAsesoramientoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_send_an_advisory_request_with_only_a_message(): void
    {
        $user = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/solicitudes-asesoramiento', [
            'mensaje' => 'Necesito ayuda para configurar las zonas de reparto.',
        ])->assertCreated()
            ->assertJsonPath('data.usuario_id', $user->id)
            ->assertJsonPath('data.datos_usuario.nombre_completo', $user->nombre_completo)
            ->assertJsonPath('data.datos_usuario.email', $user->email)
            ->assertJsonPath('data.mensaje', 'Necesito ayuda para configurar las zonas de reparto.')
            ->assertJsonPath('data.leido', false);

        $this->assertDatabaseHas('solicitudes_asesoramiento', [
            'usuario_id' => $user->id,
            'correo_corporativo' => $user->email,
            'mensaje' => 'Necesito ayuda para configurar las zonas de reparto.',
            'leido' => false,
        ]);
    }

    public function test_message_is_required(): void
    {
        Sanctum::actingAs(User::where('email', 'chofer@logistica.com')->firstOrFail());
        $this->postJson('/api/v1/solicitudes-asesoramiento')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('mensaje');
    }

    public function test_unauthenticated_user_cannot_send_an_advisory_request(): void
    {
        $this->postJson('/api/v1/solicitudes-asesoramiento', [
            'mensaje' => 'Mensaje sin autenticacion',
        ])->assertUnauthorized();
    }
}

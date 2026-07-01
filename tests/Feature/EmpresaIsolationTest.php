<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmpresaIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_only_lists_own_empresa_and_users(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/empresas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $admin->empresa_id);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonMissing(['empresa_id' => Empresa::whereKeyNot($admin->empresa_id)->firstOrFail()->id]);
    }

    public function test_admin_cannot_read_or_modify_resources_from_another_empresa(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $otherUser = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/empresas/{$otherUser->empresa_id}")->assertNotFound();
        $this->patchJson("/api/v1/empresas/{$otherUser->empresa_id}", [
            'razon_social' => 'Empresa intervenida',
        ])->assertNotFound();
        $this->getJson("/api/v1/users/{$otherUser->id}")->assertNotFound();
        $this->patchJson("/api/v1/users/{$otherUser->id}", [
            'nombre_completo' => 'Usuario intervenido',
        ])->assertNotFound();
    }

    public function test_admin_cannot_choose_empresa_when_creating_user(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $otherEmpresa = Empresa::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/users', [
            'empresa_id' => $otherEmpresa->id,
            'rol_id' => User::ROL_ADMIN,
            'nombre_completo' => 'Otro Administrador',
            'email' => 'otro-admin@example.com',
            'password' => 'password-segura',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('empresa_id');

        $this->assertDatabaseMissing('users', [
            'email' => 'otro-admin@example.com',
        ]);
    }
}

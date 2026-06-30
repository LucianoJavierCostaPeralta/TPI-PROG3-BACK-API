<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RegistroEmpresaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistroEmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_empresa_can_register_with_an_admin_user(): void
    {
        $response = $this->postJson('/api/v1/registro', $this->validPayload());

        $response
            ->assertCreated()
            ->assertJsonPath('data.empresa.razon_social', 'Transportes UTN')
            ->assertJsonPath('data.user.nombre_completo', 'Administrador')
            ->assertJsonPath('data.user.rol_id', User::ROL_ADMIN)
            ->assertJsonStructure(['token']);

        $empresaId = $response->json('data.empresa.id');

        $this->assertDatabaseHas('empresas', [
            'id' => $empresaId,
            'cuit' => '20123456789',
            'tamano_flota' => '1-10',
        ]);
        $this->assertDatabaseHas('users', [
            'empresa_id' => $empresaId,
            'email' => 'admin@empresa.com',
            'rol_id' => User::ROL_ADMIN,
        ]);

        $user = User::where('email', 'admin@empresa.com')->firstOrFail();

        $this->assertTrue(Hash::check('123456', $user->password));
        $this->assertNotNull($user->tokens()->first());
    }

    public function test_registration_rejects_duplicate_cuit_and_email(): void
    {
        $this->postJson('/api/v1/registro', $this->validPayload())->assertCreated();

        $this->postJson('/api/v1/registro', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cuit', 'email']);
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $payload = $this->validPayload();
        $payload['terminos_aceptados'] = false;

        $this->postJson('/api/v1/registro', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('terminos_aceptados');
    }

    public function test_registration_rolls_back_company_when_user_creation_fails(): void
    {
        $payload = $this->validPayload();
        $payload['email'] = 'admin@admin.com';

        try {
            app(RegistroEmpresaService::class)->registrar($payload);
            $this->fail('Se esperaba un error de clave unica al crear el usuario.');
        } catch (QueryException) {
            $this->assertDatabaseMissing('empresas', [
                'cuit' => '20123456789',
            ]);
        }
    }

    public function test_company_cannot_be_created_outside_registration_flow(): void
    {
        $this->postJson('/api/v1/empresas', [
            'razon_social' => 'Empresa sin administrador',
            'cuit' => '20999999999',
            'email_contacto' => 'empresa@example.com',
            'telefono' => '3510000000',
        ])->assertMethodNotAllowed();

        $this->assertDatabaseMissing('empresas', [
            'cuit' => '20999999999',
        ]);
    }

    public function test_registration_is_rate_limited(): void
    {
        $payload = $this->validPayload();
        $payload['terminos_aceptados'] = false;

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/registro', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/v1/registro', $payload)->assertTooManyRequests();
    }

    private function validPayload(): array
    {
        return [
            'razon_social' => 'Transportes UTN',
            'cuit' => '20123456789',
            'email' => 'admin@empresa.com',
            'password' => '123456',
            'telefono' => '3511234567',
            'tamano_flota' => '1-10',
            'terminos_aceptados' => true,
        ];
    }
}

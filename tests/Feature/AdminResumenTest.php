<?php

namespace Tests\Feature;

use App\Models\ClienteDestinatario;
use App\Models\Entrega;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminResumenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_get_home_summary_for_own_company(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $companyId = $admin->empresa_id;
        Sanctum::actingAs($admin);

        User::factory()->admin()->create([
            'empresa_id' => $companyId,
            'email' => 'segundo-admin@example.com',
            'nombre_completo' => 'Segundo Admin',
        ]);

        $driverOne = User::factory()->chofer()->create([
            'empresa_id' => $companyId,
            'email' => 'chofer-uno@example.com',
            'nombre_completo' => 'Chofer Uno',
        ]);
        User::factory()->chofer()->create([
            'empresa_id' => $companyId,
            'email' => 'chofer-dos@example.com',
            'nombre_completo' => 'Chofer Dos',
        ]);

        Entrega::factory()->create([
            'empresa_id' => $companyId,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $companyId,
            ])->id,
            'cliente' => 'Cliente 1',
            'cliente_dni' => '30111111',
            'producto' => 'Producto 1',
        ]);
        Entrega::factory()->assignedTo($driverOne)->create([
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $companyId,
            ])->id,
            'cliente' => 'Cliente 2',
            'cliente_dni' => '30222222',
            'producto' => 'Producto 2',
        ]);

        $otherCompanyAdmin = User::factory()->admin()->create();
        $otherCompanyDriver = User::factory()->chofer()->create([
            'empresa_id' => $otherCompanyAdmin->empresa_id,
        ]);
        Entrega::factory()->assignedTo($otherCompanyDriver)->create([
            'empresa_id' => $otherCompanyAdmin->empresa_id,
            'cliente_id' => ClienteDestinatario::factory()->create([
                'empresa_id' => $otherCompanyAdmin->empresa_id,
            ])->id,
            'cliente' => 'Cliente externo',
            'cliente_dni' => '30999999',
            'producto' => 'Producto externo',
        ]);

        $response = $this->getJson('/api/v1/admin/resumen');

        $response
            ->assertOk()
            ->assertJsonPath('data.profile.id', $admin->id)
            ->assertJsonPath('data.company.id', $companyId)
            ->assertJsonCount(3, 'data.drivers')
            ->assertJsonCount(2, 'data.admins')
            ->assertJsonCount(2, 'data.orders')
            ->assertJsonMissingPath('data.drivers.3')
            ->assertJsonMissingPath('data.admins.2')
            ->assertJsonMissingPath('data.orders.2');

        $this->assertSame($admin->email, $response->json('data.profile.email'));
        $this->assertSame('Logística Central', $response->json('data.company.razon_social'));
    }
}

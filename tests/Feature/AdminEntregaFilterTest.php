<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminEntregaFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_filter_entregas_by_estado_and_chofer(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($admin);
        Entrega::factory()->assignedTo($chofer)->count(2)->create();
        Entrega::factory()->create(['empresa_id' => $admin->empresa_id]);

        $this->getJson('/api/v1/admin/entregas?estado_id='.Entrega::ESTADO_ASSIGNED."&chofer_id={$chofer->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.data.0.chofer_id', $chofer->id);
    }

    public function test_admin_can_filter_unassigned_entregas(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($admin);
        Entrega::factory()->create(['empresa_id' => $admin->empresa_id]);
        Entrega::factory()->assignedTo($chofer)->create();

        $this->getJson('/api/v1/admin/entregas?sin_chofer=1')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.chofer_id', null);
    }

    public function test_admin_listing_is_paginated_and_scoped_to_own_empresa(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        Sanctum::actingAs($admin);
        Entrega::factory()->count(3)->create(['empresa_id' => $admin->empresa_id]);
        Entrega::factory()->create();

        $this->getJson('/api/v1/admin/entregas?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.total', 3)
            ->assertJsonPath('data.per_page', 2);
    }

    public function test_admin_cannot_filter_by_chofer_from_another_empresa(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $otherChofer = User::factory()->chofer()->create();
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/admin/entregas?chofer_id={$otherChofer->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('chofer_id');
    }
}

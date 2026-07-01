<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_chofer_can_recover_password_using_email_and_dni(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $chofer->update([
            'dni' => '30123456',
            'password' => 'password-anterior',
        ]);
        $chofer->createToken('old-token');

        $this->postJson('/api/v1/recuperar-password', [
            'email' => $chofer->email,
        ])->assertOk();

        $chofer->refresh();

        $this->assertTrue(Hash::check('30123456', $chofer->password));
        $this->assertCount(0, $chofer->tokens);
    }

    public function test_recovery_does_not_reveal_if_email_exists(): void
    {
        $this->postJson('/api/v1/recuperar-password', [
            'email' => 'inexistente@example.com',
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Si el correo pertenece a un chofer registrado, la contrasena fue restablecida.',
            );
    }

    public function test_recovery_does_not_reset_admin_password(): void
    {
        $admin = User::where('email', 'admin@admin.com')->firstOrFail();
        $password = $admin->password;

        $this->postJson('/api/v1/recuperar-password', [
            'email' => $admin->email,
        ])->assertOk();

        $this->assertSame($password, $admin->fresh()->password);
    }

    public function test_authenticated_user_can_change_password_from_profile(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        $chofer->update(['password' => 'password-actual']);
        Sanctum::actingAs($chofer);

        $this->patchJson('/api/v1/profile/password', [
            'current_password' => 'password-actual',
            'password' => 'password-nueva',
            'password_confirmation' => 'password-nueva',
        ])->assertOk();

        $this->assertTrue(Hash::check('password-nueva', $chofer->fresh()->password));
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($chofer);

        $this->patchJson('/api/v1/profile/password', [
            'current_password' => 'incorrecta',
            'password' => 'password-nueva',
            'password_confirmation' => 'password-nueva',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_password_change_requires_confirmation(): void
    {
        $chofer = User::where('email', 'chofer@logistica.com')->firstOrFail();
        Sanctum::actingAs($chofer);

        $this->patchJson('/api/v1/profile/password', [
            'current_password' => 'password',
            'password' => 'password-nueva',
            'password_confirmation' => 'otra-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}

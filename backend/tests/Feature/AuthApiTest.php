<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_retorna_token_com_credenciais_validas(): void
    {
        $this->seed(AdminUserSeeder::class);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@admin.com',
            'password' => '123qwe!!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJsonPath('user.email', 'admin@admin.com');
    }

    public function test_login_retorna_nao_autorizado_com_senha_invalida(): void
    {
        $this->seed(AdminUserSeeder::class);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@admin.com',
            'password' => 'senha-errada',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Credenciais invalidas.',
            ]);
    }

    public function test_login_retorna_erros_de_validacao_com_payload_invalido(): void
    {
        $response = $this->postJson('/api/login', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_me_exige_autenticacao(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_retorna_usuario_autenticado(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Administrador',
                    'email' => 'admin@admin.com',
                ],
            ]);
    }

    public function test_logout_revoga_o_token_atual(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Logout realizado com sucesso.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_seeder_do_usuario_admin_e_idempotente(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@admin.com',
            'name' => 'Administrador',
        ]);
    }
}

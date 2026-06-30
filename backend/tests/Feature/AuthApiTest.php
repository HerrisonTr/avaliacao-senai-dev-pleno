<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_retorna_token_com_credenciais_validas(): void
    {
        $this->seed(DatabaseSeeder::class);

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
        $this->seed(DatabaseSeeder::class);

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
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@admin.com')->firstOrFail();

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
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@admin.com')->firstOrFail();
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
}

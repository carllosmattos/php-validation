<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se um cliente pode fazer login com credenciais válidas.
     */
    public function test_client_can_login_with_valid_credentials(): void
    {
        // Arrange: Cria um cliente no banco
        $client = Client::factory()->create([
            'email' => 'teste@exemplo.com',
            'password' => bcrypt('senha123'),
        ]);

        // Act: Envia requisição de login
        $response = $this->postJson('/api/login', [
            'email' => 'teste@exemplo.com',
            'password' => 'senha123',
        ]);

        // Assert: Verifica resposta e token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'client' => [
                        'id',
                        'name',
                        'email',
                        'cpf',
                        'phone',
                        'birth_date',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $client->id,
            'tokenable_type' => Client::class,
        ]);
    }

    /**
     * Testa se login falha com credenciais inválidas.
     */
    public function test_client_cannot_login_with_invalid_credentials(): void
    {
        Client::factory()->create([
            'email' => 'teste@exemplo.com',
            'password' => bcrypt('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@exemplo.com',
            'password' => 'senhaerrada',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa se cliente inativo não pode fazer login.
     */
    public function test_inactive_client_cannot_login(): void
    {
        Client::factory()->create([
            'email' => 'teste@exemplo.com',
            'password' => bcrypt('senha123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@exemplo.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa validação de campos obrigatórios no login.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Testa se cliente autenticado pode acessar o endpoint /me.
     */
    public function test_authenticated_client_can_access_profile(): void
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $client->id,
                'email' => $client->email,
            ]);
    }

    /**
     * Testa se requisição sem token é rejeitada.
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Não autenticado');
    }

    /**
     * Testa se cliente pode fazer logout.
     */
    public function test_client_can_logout(): void
    {
        $client = Client::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso.',
            ]);

        // Verifica se o token foi deletado
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $client->id,
        ]);
    }
}

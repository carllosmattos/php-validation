<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se pode listar clientes com paginação.
     */
    public function test_can_list_clients(): void
    {
        $client = Client::factory()->create();
        Client::factory()->count(5)->create();

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                'links',
                'meta',
            ])
            ->assertJsonCount(6, 'data'); // 1 autenticado + 5 criados
    }

    /**
     * Testa se pode criar um cliente com dados válidos.
     */
    public function test_can_create_client(): void
    {
        $client = Client::factory()->create();

        $newClientData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'cpf' => '12345678901',
            'phone' => '11987654321',
            'birth_date' => '1990-05-15',
            'password' => 'senha123',
            'is_active' => true,
        ];

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/clients', $newClientData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'João Silva',
                    'email' => 'joao@exemplo.com',
                    'cpf' => '12345678901',
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'joao@exemplo.com',
            'cpf' => '12345678901',
        ]);
    }

    /**
     * Testa validação de campos obrigatórios ao criar cliente.
     */
    public function test_create_client_requires_all_fields(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/clients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'cpf',
                'phone',
                'birth_date',
                'password',
            ]);
    }

    /**
     * Testa se não permite criar cliente com email duplicado.
     */
    public function test_cannot_create_client_with_duplicate_email(): void
    {
        $client = Client::factory()->create();
        $existingClient = Client::factory()->create([
            'email' => 'duplicado@exemplo.com',
        ]);

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/clients', [
                'name' => 'Outro Nome',
                'email' => 'duplicado@exemplo.com',
                'cpf' => '98765432100',
                'phone' => '11999999999',
                'birth_date' => '1995-03-20',
                'password' => 'senha123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa se não permite criar cliente com CPF duplicado.
     */
    public function test_cannot_create_client_with_duplicate_cpf(): void
    {
        $client = Client::factory()->create();
        $existingClient = Client::factory()->create([
            'cpf' => '11111111111',
        ]);

        $response = $this->actingAs($client, 'sanctum')
            ->postJson('/api/clients', [
                'name' => 'Outro Nome',
                'email' => 'outro@exemplo.com',
                'cpf' => '11111111111',
                'phone' => '11999999999',
                'birth_date' => '1995-03-20',
                'password' => 'senha123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    /**
     * Testa se pode visualizar um cliente específico.
     */
    public function test_can_show_client(): void
    {
        $authenticatedClient = Client::factory()->create();
        $targetClient = Client::factory()->create([
            'name' => 'Cliente Alvo',
            'email' => 'alvo@exemplo.com',
        ]);

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->getJson("/api/clients/{$targetClient->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $targetClient->id,
                    'name' => 'Cliente Alvo',
                    'email' => 'alvo@exemplo.com',
                ],
            ]);
    }

    /**
     * Testa se retorna 404 para cliente inexistente.
     */
    public function test_show_returns_404_for_non_existent_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients/99999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Endpoint não encontrado');
    }

    /**
     * Testa se pode atualizar um cliente.
     */
    public function test_can_update_client(): void
    {
        $authenticatedClient = Client::factory()->create();
        $targetClient = Client::factory()->create([
            'name' => 'Nome Original',
            'email' => 'original@exemplo.com',
        ]);

        $updateData = [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@exemplo.com',
        ];

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->putJson("/api/clients/{$targetClient->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Nome Atualizado',
                    'email' => 'atualizado@exemplo.com',
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'id' => $targetClient->id,
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@exemplo.com',
        ]);
    }

    /**
     * Testa se pode atualizar parcialmente um cliente.
     */
    public function test_can_partially_update_client(): void
    {
        $authenticatedClient = Client::factory()->create();
        $targetClient = Client::factory()->create([
            'name' => 'Nome Original',
            'phone' => '11111111111',
        ]);

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->putJson("/api/clients/{$targetClient->id}", [
                'phone' => '22222222222',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'id' => $targetClient->id,
            'name' => 'Nome Original', // Nome não mudou
            'phone' => '22222222222', // Telefone atualizado
        ]);
    }

    /**
     * Testa se pode deletar (soft delete) um cliente.
     */
    public function test_can_delete_client(): void
    {
        $authenticatedClient = Client::factory()->create();
        $targetClient = Client::factory()->create();

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->deleteJson("/api/clients/{$targetClient->id}");

        $response->assertStatus(204);

        // Verifica se foi soft deleted
        $this->assertSoftDeleted('clients', [
            'id' => $targetClient->id,
        ]);
    }

    /**
     * Testa se requisições não autenticadas são rejeitadas.
     */
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $client = Client::factory()->create();

        $this->getJson('/api/clients')->assertStatus(401);
        $this->postJson('/api/clients', [])->assertStatus(401);
        $this->getJson("/api/clients/{$client->id}")->assertStatus(401);
        $this->putJson("/api/clients/{$client->id}", [])->assertStatus(401);
        $this->deleteJson("/api/clients/{$client->id}")->assertStatus(401);
    }
}

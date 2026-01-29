<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientFilterAndSortTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa filtro por nome (busca parcial com LIKE).
     */
    public function test_can_filter_by_name(): void
    {
        $client = Client::factory()->create();
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria João']);
        Client::factory()->create(['name' => 'Pedro Santos']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?name=João');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // João Silva e Maria João
    }

    /**
     * Testa filtro por email (busca parcial com LIKE).
     */
    public function test_can_filter_by_email(): void
    {
        $client = Client::factory()->create();
        Client::factory()->create(['email' => 'joao@gmail.com']);
        Client::factory()->create(['email' => 'maria@gmail.com']);
        Client::factory()->create(['email' => 'pedro@hotmail.com']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?email=gmail');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Dois emails @gmail.com
    }

    /**
     * Testa filtro por CPF (busca exata).
     */
    public function test_can_filter_by_cpf(): void
    {
        $client = Client::factory()->create();
        $targetClient = Client::factory()->create(['cpf' => '12345678901']);
        Client::factory()->create(['cpf' => '98765432100']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?cpf=12345678901');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['cpf' => '12345678901'],
                ],
            ]);
    }

    /**
     * Testa filtro por telefone (busca parcial com LIKE).
     */
    public function test_can_filter_by_phone(): void
    {
        $client = Client::factory()->create();
        Client::factory()->create(['phone' => '11987654321']);
        Client::factory()->create(['phone' => '11999999999']);
        Client::factory()->create(['phone' => '21987654321']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?phone=119');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Dois telefones começam com 119
    }

    /**
     * Testa filtro por status ativo (is_active).
     */
    public function test_can_filter_by_active_status(): void
    {
        $client = Client::factory()->create();
        Client::factory()->count(3)->create(['is_active' => true]);
        Client::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?is_active=1');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data'); // 1 autenticado + 3 ativos
    }

    /**
     * Testa filtro por status inativo (is_active).
     */
    public function test_can_filter_by_inactive_status(): void
    {
        $client = Client::factory()->create();
        Client::factory()->count(3)->create(['is_active' => true]);
        Client::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?is_active=0');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Apenas os 2 inativos
    }

    /**
     * Testa filtro por data de nascimento inicial (from_birth_date).
     */
    public function test_can_filter_by_birth_date_from(): void
    {
        $client = Client::factory()->create();
        Client::factory()->create(['birth_date' => '1980-01-01']);
        Client::factory()->create(['birth_date' => '1990-06-15']);
        Client::factory()->create(['birth_date' => '2000-12-31']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?from_birth_date=1990-01-01');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 1990 e 2000
    }

    /**
     * Testa filtro por data de nascimento final (to_birth_date).
     */
    public function test_can_filter_by_birth_date_to(): void
    {
        $client = Client::factory()->create();
        Client::factory()->create(['birth_date' => '1980-01-01']);
        Client::factory()->create(['birth_date' => '1990-06-15']);
        Client::factory()->create(['birth_date' => '2000-12-31']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?to_birth_date=1990-12-31');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 1980 e 1990
    }

    /**
     * Testa filtro por intervalo de datas de criação.
     */
    public function test_can_filter_by_created_at_range(): void
    {
        $client = Client::factory()->create();

        // Cria clientes com datas específicas
        Client::factory()->create(['created_at' => '2024-01-01 10:00:00']);
        Client::factory()->create(['created_at' => '2024-06-15 10:00:00']);
        Client::factory()->create(['created_at' => '2024-12-31 10:00:00']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?from_created_at=2024-06-01&to_created_at=2024-12-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data'); // Apenas o de junho
    }

    /**
     * Testa ordenação por nome ascendente.
     */
    public function test_can_sort_by_name_ascending(): void
    {
        $client = Client::factory()->create(['name' => 'Zé']);
        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Maria']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?sort_by=name&sort_order=asc');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Ana', 'Maria', 'Zé'], $names);
    }

    /**
     * Testa ordenação por email descendente.
     */
    public function test_can_sort_by_email_descending(): void
    {
        $client = Client::factory()->create(['email' => 'a@test.com']);
        Client::factory()->create(['email' => 'c@test.com']);
        Client::factory()->create(['email' => 'b@test.com']);

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?sort_by=email&sort_order=desc');

        $response->assertStatus(200);

        $emails = collect($response->json('data'))->pluck('email')->toArray();
        $this->assertEquals(['c@test.com', 'b@test.com', 'a@test.com'], $emails);
    }

    /**
     * Testa ordenação por data de criação (mais recentes primeiro).
     */
    public function test_can_sort_by_created_at_descending(): void
    {
        $client = Client::factory()->create();
        sleep(1);
        $oldest = Client::factory()->create();
        sleep(1);
        $middle = Client::factory()->create();
        sleep(1);
        $newest = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?sort_by=created_at&sort_order=desc');

        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        // Mais recentes primeiro: newest, middle, oldest, client original
        $this->assertEquals($newest->id, $ids[0]);
        $this->assertEquals($middle->id, $ids[1]);
        $this->assertEquals($oldest->id, $ids[2]);
    }

    /**
     * Testa que campos inválidos na ordenação usam padrão (id desc).
     */
    public function test_invalid_sort_field_uses_default(): void
    {
        $client = Client::factory()->create();
        Client::factory()->count(3)->create();

        // Campo inválido não está na whitelist
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?sort_by=password&sort_order=asc');

        $response->assertStatus(200); // Não dá erro, usa padrão

        // Verifica que está ordenado por ID descendente (padrão)
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $sortedIds = $ids;
        rsort($sortedIds); // Ordena descendente
        $this->assertEquals($sortedIds, $ids); // IDs em ordem decrescente
    }

    /**
     * Testa combinação de múltiplos filtros.
     */
    public function test_can_combine_multiple_filters(): void
    {
        $client = Client::factory()->create();

        // Cliente que atende todos os critérios
        $match = Client::factory()->create([
            'name' => 'João Silva Teste',
            'email' => 'joao@gmail.com',
            'is_active' => true,
            'birth_date' => '1990-05-15',
        ]);

        // Clientes que não atendem todos os critérios
        Client::factory()->create(['name' => 'João Santos', 'email' => 'joao@hotmail.com', 'is_active' => true]);
        Client::factory()->create(['name' => 'Maria Silva', 'email' => 'maria@gmail.com', 'is_active' => true]);
        Client::factory()->create(['name' => 'Pedro Silva', 'email' => 'pedro@gmail.com', 'is_active' => false]);

        // Filtros mais específicos para pegar apenas o match
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?name=Silva Teste&email=gmail&is_active=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $match->id],
                ],
            ]);
    }

    /**
     * Testa paginação customizada (per_page).
     */
    public function test_can_customize_pagination(): void
    {
        $client = Client::factory()->create();
        Client::factory()->count(25)->create();

        // Solicita 10 por página
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10);
    }

    /**
     * Testa que soft deleted não aparecem na listagem.
     */
    public function test_soft_deleted_clients_are_not_listed(): void
    {
        $client = Client::factory()->create();
        $activeClient = Client::factory()->create();
        $deletedClient = Client::factory()->create();

        $deletedClient->delete(); // Soft delete

        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Apenas os 2 ativos

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($deletedClient->id, $ids);
    }
}

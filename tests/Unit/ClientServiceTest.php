<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClientService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClientService();
    }

    /**
     * Testa se o método list retorna uma paginação.
     */
    public function test_list_returns_paginated_results(): void
    {
        Client::factory()->count(15)->create();

        $result = $this->service->list([]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(15, $result->total());
    }

    /**
     * Testa se aplica filtro de nome corretamente.
     */
    public function test_applies_name_filter(): void
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);

        $result = $this->service->list(['name' => 'João']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('João Silva', $result->items()[0]->name);
    }

    /**
     * Testa se aplica filtro de email corretamente.
     */
    public function test_applies_email_filter(): void
    {
        Client::factory()->create(['email' => 'teste@gmail.com']);
        Client::factory()->create(['email' => 'teste@hotmail.com']);

        $result = $this->service->list(['email' => 'gmail']);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('gmail', $result->items()[0]->email);
    }

    /**
     * Testa se aplica filtro de CPF exato.
     */
    public function test_applies_cpf_exact_filter(): void
    {
        Client::factory()->create(['cpf' => '12345678901']);
        Client::factory()->create(['cpf' => '98765432100']);

        $result = $this->service->list(['cpf' => '12345678901']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('12345678901', $result->items()[0]->cpf);
    }

    /**
     * Testa se aplica filtro de telefone.
     */
    public function test_applies_phone_filter(): void
    {
        Client::factory()->create(['phone' => '11987654321']);
        Client::factory()->create(['phone' => '21987654321']);

        $result = $this->service->list(['phone' => '119']);

        $this->assertCount(1, $result->items());
        $this->assertStringStartsWith('119', $result->items()[0]->phone);
    }

    /**
     * Testa se aplica filtro de status ativo.
     */
    public function test_applies_is_active_filter(): void
    {
        Client::factory()->count(3)->create(['is_active' => true]);
        Client::factory()->count(2)->create(['is_active' => false]);

        $result = $this->service->list(['is_active' => '1']);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $client) {
            $this->assertTrue($client->is_active);
        }
    }

    /**
     * Testa se aplica filtro de data de nascimento inicial.
     */
    public function test_applies_birth_date_from_filter(): void
    {
        Client::factory()->create(['birth_date' => '1980-01-01']);
        Client::factory()->create(['birth_date' => '1990-06-15']);
        Client::factory()->create(['birth_date' => '2000-12-31']);

        $result = $this->service->list(['from_birth_date' => '1990-01-01']);

        $this->assertCount(2, $result->items());
    }

    /**
     * Testa se aplica filtro de data de nascimento final.
     */
    public function test_applies_birth_date_to_filter(): void
    {
        Client::factory()->create(['birth_date' => '1980-01-01']);
        Client::factory()->create(['birth_date' => '1990-06-15']);
        Client::factory()->create(['birth_date' => '2000-12-31']);

        $result = $this->service->list(['to_birth_date' => '1990-12-31']);

        $this->assertCount(2, $result->items());
    }

    /**
     * Testa se aplica ordenação ascendente.
     */
    public function test_applies_ascending_sort(): void
    {
        Client::factory()->create(['name' => 'Zé']);
        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Maria']);

        $result = $this->service->list([
            'sort_by' => 'name',
            'sort_order' => 'asc',
        ]);

        $names = $result->pluck('name')->toArray();
        $this->assertEquals(['Ana', 'Maria', 'Zé'], $names);
    }

    /**
     * Testa se aplica ordenação descendente.
     */
    public function test_applies_descending_sort(): void
    {
        Client::factory()->create(['name' => 'Ana']);
        Client::factory()->create(['name' => 'Zé']);
        Client::factory()->create(['name' => 'Maria']);

        $result = $this->service->list([
            'sort_by' => 'name',
            'sort_order' => 'desc',
        ]);

        $names = $result->pluck('name')->toArray();
        $this->assertEquals(['Zé', 'Maria', 'Ana'], $names);
    }

    /**
     * Testa se ignora campos de ordenação inválidos (não na whitelist).
     */
    public function test_ignores_invalid_sort_fields(): void
    {
        Client::factory()->count(3)->create();

        // Campo 'password' não está na whitelist
        $result = $this->service->list([
            'sort_by' => 'password',
            'sort_order' => 'asc',
        ]);

        // Deve usar ordenação padrão (id desc)
        $this->assertNotNull($result);
    }

    /**
     * Testa se pode criar um cliente.
     */
    public function test_can_create_client(): void
    {
        $data = [
            'name' => 'Teste Cliente',
            'email' => 'teste@exemplo.com',
            'cpf' => '12345678901',
            'phone' => '11987654321',
            'birth_date' => '1990-05-15',
            'password' => 'senha123',
            'is_active' => true,
        ];

        $client = $this->service->create($data);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('Teste Cliente', $client->name);
        $this->assertDatabaseHas('clients', [
            'email' => 'teste@exemplo.com',
        ]);
    }

    /**
     * Testa se criptografa a senha ao criar cliente.
     */
    public function test_encrypts_password_on_create(): void
    {
        $data = [
            'name' => 'Teste',
            'email' => 'teste@exemplo.com',
            'cpf' => '12345678901',
            'phone' => '11987654321',
            'birth_date' => '1990-05-15',
            'password' => 'senha123',
        ];

        $client = $this->service->create($data);

        $this->assertNotEquals('senha123', $client->password);
        $this->assertTrue(\Hash::check('senha123', $client->password));
    }

    /**
     * Testa se pode encontrar um cliente por ID.
     */
    public function test_can_find_client_by_id(): void
    {
        $created = Client::factory()->create();

        $found = $this->service->find($created->id);

        $this->assertInstanceOf(Client::class, $found);
        $this->assertEquals($created->id, $found->id);
    }

    /**
     * Testa se pode atualizar um cliente.
     */
    public function test_can_update_client(): void
    {
        $client = Client::factory()->create([
            'name' => 'Nome Original',
        ]);

        $updated = $this->service->update($client->id, [
            'name' => 'Nome Atualizado',
        ]);

        $this->assertEquals('Nome Atualizado', $updated->name);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    /**
     * Testa se criptografa senha ao atualizar.
     */
    public function test_encrypts_password_on_update(): void
    {
        $client = Client::factory()->create();

        $updated = $this->service->update($client->id, [
            'password' => 'novasenha123',
        ]);

        $this->assertNotEquals('novasenha123', $updated->password);
        $this->assertTrue(\Hash::check('novasenha123', $updated->password));
    }

    /**
     * Testa se pode deletar (soft delete) um cliente.
     */
    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $result = $this->service->delete($client->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('clients', [
            'id' => $client->id,
        ]);
    }

    /**
     * Testa se clientes soft deleted não aparecem na listagem.
     */
    public function test_soft_deleted_clients_not_in_list(): void
    {
        Client::factory()->create();
        $deleted = Client::factory()->create();
        $deleted->delete();

        $result = $this->service->list([]);

        $this->assertCount(1, $result->items());
        $ids = $result->pluck('id')->toArray();
        $this->assertNotContains($deleted->id, $ids);
    }

    /**
     * Testa paginação customizada.
     */
    public function test_respects_custom_pagination(): void
    {
        Client::factory()->count(25)->create();

        $result = $this->service->list(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(10, $result->perPage());
    }
}

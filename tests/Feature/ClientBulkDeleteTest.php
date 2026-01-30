<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa deleção em massa de clientes.
     */
    public function test_can_delete_multiple_clients(): void
    {
        $authenticatedClient = Client::factory()->create();
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $client3 = Client::factory()->create();

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => [$client1->id, $client2->id, $client3->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'deleted_count' => 3,
            ]);

        $this->assertSoftDeleted('clients', ['id' => $client1->id]);
        $this->assertSoftDeleted('clients', ['id' => $client2->id]);
        $this->assertSoftDeleted('clients', ['id' => $client3->id]);
    }

    /**
     * Testa deleção em massa requer array de IDs.
     */
    public function test_bulk_delete_requires_ids_array(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }

    /**
     * Testa deleção em massa requer pelo menos 1 ID.
     */
    public function test_bulk_delete_requires_at_least_one_id(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }

    /**
     * Testa deleção em massa valida que IDs existem.
     */
    public function test_bulk_delete_validates_existing_ids(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => [999, 1000, 1001],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0', 'ids.1', 'ids.2']);
    }

    /**
     * Testa deleção em massa requer autenticação.
     */
    public function test_bulk_delete_requires_authentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->deleteJson('/api/clients', [
            'ids' => [$client->id],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Testa deleção em massa com IDs parcialmente válidos.
     */
    public function test_bulk_delete_with_partial_valid_ids(): void
    {
        $client = Client::factory()->create();
        $client1 = Client::factory()->create();

        $response = $this->actingAs($client, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => [$client1->id, 999],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids.1']);
    }

    /**
     * Testa deleção em massa de um único cliente.
     */
    public function test_can_delete_single_client_via_bulk_endpoint(): void
    {
        $authenticatedClient = Client::factory()->create();
        $clientToDelete = Client::factory()->create();

        $response = $this->actingAs($authenticatedClient, 'sanctum')
            ->deleteJson('/api/clients', [
                'ids' => [$clientToDelete->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'deleted_count' => 1,
            ]);

        $this->assertSoftDeleted('clients', ['id' => $clientToDelete->id]);
    }
}

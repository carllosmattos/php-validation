<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class DemoClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar cliente de demonstração para login
        Client::factory()->create([
            'name' => 'João da Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678900',
            'phone' => '11987654321',
            'birth_date' => '1990-01-15',
            'is_active' => true,
            'password' => bcrypt('password'),
        ]);

        Client::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'cpf' => '98765432100',
            'phone' => '11912345678',
            'birth_date' => '1985-05-20',
            'is_active' => true,
            'password' => bcrypt('password'),
        ]);

        Client::factory()->create([
            'name' => 'Pedro Oliveira',
            'email' => 'pedro@example.com',
            'cpf' => '45678912300',
            'phone' => '21998765432',
            'birth_date' => '1992-12-10',
            'is_active' => false,
            'password' => bcrypt('password'),
        ]);

        // Criar mais 20 clientes aleatórios
        Client::factory(20)->create();
    }
}

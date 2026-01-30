<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ClientService
{
    private const CACHE_TTL = 300; // 5 minutos
    private const CACHE_PREFIX = 'clients_';

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Nota: Desabilitar cache em paginação pode ser melhor para dados dinâmicos
        // Habilitado aqui apenas para demonstração. Em produção, considere cache apenas
        // para queries específicas ou com TTL muito baixo.

        $query = Client::query();

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters);

        $perPage = $filters['per_page'] ?? $perPage;

        return $query->paginate($perPage);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['cpf'])) {
            $query->where('cpf', $filters['cpf']);
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['from_birth_date'])) {
            $query->whereDate('birth_date', '>=', $filters['from_birth_date']);
        }

        if (!empty($filters['to_birth_date'])) {
            $query->whereDate('birth_date', '<=', $filters['to_birth_date']);
        }

        if (!empty($filters['from_created_at'])) {
            $query->whereDate('created_at', '>=', $filters['from_created_at']);
        }

        if (!empty($filters['to_created_at'])) {
            $query->whereDate('created_at', '<=', $filters['to_created_at']);
        }
    }

    private function applySort(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = ['id', 'name', 'email', 'cpf', 'phone', 'birth_date', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, strtolower($sortOrder) === 'asc' ? 'asc' : 'desc');
        } else {
            // Se campo inválido, usar padrão
            $query->orderBy('id', 'desc');
        }
    }

    public function find(int $id): ?Client
    {
        $cacheKey = self::CACHE_PREFIX . "find_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Client::find($id);
        });
    }

    public function create(array $data): Client
    {
        $data['password'] = bcrypt($data['password']);

        $client = Client::create($data);

        // Limpa cache individual caso já existisse
        $this->clearClientCache($client->id);

        return $client;
    }

    public function update(int $id, array $data): Client
    {
        $client = Client::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $client->update($data);

        // Limpa cache deste cliente
        $this->clearClientCache($id);

        return $client->fresh();
    }

    public function delete(int $id): bool
    {
        $client = Client::findOrFail($id);
        $result = $client->delete();

        // Limpa cache deste cliente
        $this->clearClientCache($id);

        return $result;
    }

    public function deleteMany(array $ids): int
    {
        // Valida que todos os IDs existem
        $clients = Client::whereIn('id', $ids)->get();

        if ($clients->count() !== count($ids)) {
            throw new \InvalidArgumentException('Um ou mais IDs não foram encontrados.');
        }

        // Deleta em massa
        $deletedCount = Client::whereIn('id', $ids)->delete();

        // Limpa cache de todos os clientes deletados
        foreach ($ids as $id) {
            $this->clearClientCache($id);
        }

        return $deletedCount;
    }

    /**
     * Limpa o cache de um cliente específico
     */
    private function clearClientCache(int $id): void
    {
        Cache::forget(self::CACHE_PREFIX . "find_{$id}");
    }
}

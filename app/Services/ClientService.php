<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ClientService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
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
        return Client::find($id);
    }

    public function create(array $data): Client
    {
        $data['password'] = bcrypt($data['password']);

        return Client::create($data);
    }

    public function update(int $id, array $data): Client
    {
        $client = Client::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $client->update($data);

        return $client->fresh();
    }

    public function delete(int $id): bool
    {
        $client = Client::findOrFail($id);
        return $client->delete();
    }
}

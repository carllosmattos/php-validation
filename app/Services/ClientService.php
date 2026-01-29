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

        if (!empty($filters['birth_date_from'])) {
            $query->whereDate('birth_date', '>=', $filters['birth_date_from']);
        }

        if (!empty($filters['birth_date_to'])) {
            $query->whereDate('birth_date', '<=', $filters['birth_date_to']);
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }
    }

    private function applySort(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = ['id', 'name', 'email', 'cpf', 'phone', 'birth_date', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, strtolower($sortOrder) === 'asc' ? 'asc' : 'desc');
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

    public function update(Client $client, array $data): Client
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $client->update($data);

        return $client->fresh();
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }
}

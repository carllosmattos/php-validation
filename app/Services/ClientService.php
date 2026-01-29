<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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

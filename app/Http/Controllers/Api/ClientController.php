<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $clients = $this->clientService->list(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        $resource = new ClientResource($client);
        $response = $resource->toArray(request());
        $response['message'] = 'Registro criado com sucesso!';
        return response()->json($response, 201);
    }

    public function show(Client $client): ClientResource
    {
        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $updated = $this->clientService->update($client->id, $request->validated());

        return new ClientResource($updated);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->delete($client->id);

        return response()->json(null, 204);
    }

    public function destroyMany(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:clients,id',
        ]);

        try {
            $deletedCount = $this->clientService->deleteMany($request->input('ids'));

            return response()->json([
                'message' => "{$deletedCount} cliente(s) deletado(s) com sucesso.",
                'deleted_count' => $deletedCount,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}

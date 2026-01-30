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

    /**
     * @OA\Get(
     *     path="/api/clients",
     *     summary="Listar clientes",
     *     description="Retorna lista paginada de clientes com filtros e ordenação",
     *     tags={"Clientes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filtrar por nome (busca parcial)",
     *         required=false,
     *         @OA\Schema(type="string", example="João")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filtrar por email (busca parcial)",
     *         required=false,
     *         @OA\Schema(type="string", example="joao@")
     *     ),
     *     @OA\Parameter(
     *         name="cpf",
     *         in="query",
     *         description="Filtrar por CPF (exato)",
     *         required=false,
     *         @OA\Schema(type="string", example="12345678901")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Filtrar por telefone (busca parcial)",
     *         required=false,
     *         @OA\Schema(type="string", example="11999")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filtrar por status ativo/inativo",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="from_birth_date",
     *         in="query",
     *         description="Data de nascimento inicial",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="1990-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_birth_date",
     *         in="query",
     *         description="Data de nascimento final",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2000-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="from_created_at",
     *         in="query",
     *         description="Data de criação inicial",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_created_at",
     *         in="query",
     *         description="Data de criação final",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Campo para ordenação",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "name", "email", "cpf", "phone", "birth_date", "created_at", "updated_at"},
     *             example="created_at"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Direção da ordenação",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Quantidade de registros por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Client")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="per_page", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $clients = $this->clientService->list(
            filters: $request->all(),
            perPage: $request->integer('per_page', 15)
        );

        return ClientResource::collection($clients);
    }

    /**
     * @OA\Post(
     *     path="/api/clients",
     *     summary="Criar novo cliente",
     *     description="Cadastra um novo cliente no sistema",
     *     tags={"Clientes"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "cpf", "phone", "birth_date", "password"},
     *             @OA\Property(property="name", type="string", example="Maria Santos"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@uhuu.com"),
     *             @OA\Property(property="cpf", type="string", example="98765432100"),
     *             @OA\Property(property="phone", type="string", example="11988888888"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="password", type="string", format="password", example="senha123"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente criado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/clients/{id}",
     *     summary="Buscar cliente por ID",
     *     description="Retorna os dados de um cliente específico",
     *     tags={"Clientes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show(Client $client): ClientResource
    {
        return new ClientResource($client);
    }

    /**
     * @OA\Put(
     *     path="/api/clients/{id}",
     *     summary="Atualizar cliente",
     *     description="Atualiza os dados de um cliente existente",
     *     tags={"Clientes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria Silva Santos"),
     *             @OA\Property(property="email", type="string", format="email", example="maria.silva@uhuu.com"),
     *             @OA\Property(property="cpf", type="string", example="98765432100"),
     *             @OA\Property(property="phone", type="string", example="11977777777"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1995-05-15"),
     *             @OA\Property(property="password", type="string", format="password", example="novaSenha123"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente atualizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $updated = $this->clientService->update($client->id, $request->validated());

        return new ClientResource($updated);
    }

    /**
     * @OA\Delete(
     *     path="/api/clients/{id}",
     *     summary="Deletar cliente",
     *     description="Remove um cliente do sistema (soft delete)",
     *     tags={"Clientes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Cliente deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->delete($client->id);

        return response()->json(null, 204);
    }
}

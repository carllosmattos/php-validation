# UHUU - Sistema de Gestão de Clientes

Sistema para cadastro e gestão de clientes da plataforma UHUU, desenvolvido em Laravel com ambiente totalmente dockerizado.

---

## 🚀 Pré-requisitos
- Docker
- Docker Compose

---

## ⚡ Como executar o projeto

### Setup inicial

```bash
docker compose up -d --build
docker compose exec app php artisan app:setups
```

### A aplicação estará disponível em:
- **Web Application**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api-docs/

---

## 📖 Documentação da API

### Base URL
```
http://localhost:8000/api
```

### Autenticação
A API utiliza Laravel Sanctum para autenticação via Bearer Token.

#### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "joao.silva@example.com",
    "password": "password123"
  }'
```

**Resposta:**
```json
{
  "data": {
    "token": "1|abc123def456...",
    "token_type": "Bearer",
    "client": {
      "id": 1,
      "name": "João Silva",
      "email": "joao.silva@example.com",
      "cpf": "12345678901",
      "birth_date": "1990-05-15",
      "phone": "11987654321",
      "is_active": true,
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    }
  }
}
```

#### 2. Usar o token nas próximas requisições
```bash
export TOKEN="1|abc123def456..."

curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Endpoints de Clientes

#### Listar Clientes (com filtros)
```bash
curl -X GET "http://localhost:8000/api/clients?name=Silva&is_active=true&per_page=15&sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Filtros disponíveis:**
- `name` - busca parcial por nome
- `email` - busca parcial por email
- `cpf` - busca exata por CPF
- `phone` - busca parcial por telefone
- `is_active` - filtrar por status (true/false)
- `from_birth_date` - data de nascimento inicial (YYYY-MM-DD)
- `to_birth_date` - data de nascimento final (YYYY-MM-DD)
- `from_created_at` - data de criação inicial (YYYY-MM-DD)
- `to_created_at` - data de criação final (YYYY-MM-DD)
- `sort_by` - campo para ordenação (name, email, cpf, birth_date, created_at)
- `sort_order` - direção (asc, desc)
- `per_page` - resultados por página (1-100, padrão: 15)

#### Criar Cliente
```bash
curl -X POST http://localhost:8000/api/clients \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Maria Santos",
    "email": "maria.santos@example.com",
    "cpf": "98765432100",
    "password": "password123",
    "birth_date": "1995-03-20",
    "phone": "11912345678",
    "is_active": true
  }'
```

#### Buscar Cliente por ID
```bash
curl -X GET http://localhost:8000/api/clients/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

#### Atualizar Cliente
```bash
curl -X PUT http://localhost:8000/api/clients/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Maria Santos Silva",
    "email": "maria.santos@example.com",
    "cpf": "98765432100",
    "password": "newpassword123",
    "birth_date": "1995-03-20",
    "phone": "11912345678",
    "is_active": true
  }'
```

#### Deletar Cliente
```bash
curl -X DELETE http://localhost:8000/api/clients/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Logout
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Postman Collection
Importe a collection do Postman localizada em:
```
postman/Client-Management-API.postman_collection.json
```

A collection já inclui:
- Todas as rotas documentadas
- Variáveis de ambiente pré-configuradas
- Scripts automáticos para salvar o token após login
- Exemplos de requisições para todos os endpoints

### Swagger/OpenAPI
Documentação interativa disponível em:
```
http://localhost:8000/api-docs/
```

---

## 🧪 Testes

Execute os testes automatizados:
```bash
docker compose exec app php artisan test
```

---

## 🛠 Tecnologias utilizadas

- **PHP 8.4** (Laravel 11)
- **MySQL 8.0**
- **Nginx**
- **Docker / Docker Compose**
- **Laravel Sanctum** (autenticação API)
- **Swagger/OpenAPI 3.0** (documentação)

---

## 📝 Observações
- O projeto utiliza volumes montados para desenvolvimento. Em ambientes Windows, pode ser necessário garantir permissões de escrita nos diretórios `storage` e `bootstrap/cache`.
- A aplicação foi desenvolvida de forma monolítica, com API REST disponível para os principais recursos, permitindo fácil desacoplamento do frontend no futuro.
- A validação de CPF utiliza algoritmo de verificação de dígitos verificadores conforme padrão brasileiro.


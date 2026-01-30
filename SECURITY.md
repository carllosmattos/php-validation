# 🔒 Documentação de Segurança - Client Management API

## Melhorias de Segurança Implementadas

Este documento descreve as medidas de segurança implementadas na API para proteger contra ataques comuns e garantir auditoria completa.

---

## 1. Rate Limiting (Proteção contra Abuso)

### 📊 Limites Configurados:

| Endpoint | Limite | Janela | Descrição |
|----------|--------|--------|-----------|
| **API Geral** | 60 requisições | 1 minuto | Todos os endpoints autenticados |
| **Login** | 5 tentativas | 1 minuto | Por email + IP (anti brute-force) |
| **Criação de Clientes** | 10 criações | 1 minuto | Por usuário ou IP (anti-spam) |

### 🎯 Implementação:

**Arquivo:** `app/Providers/AppServiceProvider.php`

```php
// Rate limit para login (proteção contra brute-force)
RateLimiter::for('login', function (Request $request) {
    $email = (string) $request->email;
    
    return Limit::perMinute(5)->by($email . $request->ip())
        ->response(function (Request $request, array $headers) {
            return response()->json([
                'message' => 'Muitas tentativas de login. Tente novamente em 1 minuto.',
                'retry_after' => $headers['Retry-After'] ?? 60
            ], 429);
        });
});
```

**Rotas protegidas:** `routes/api.php`

```php
// Login com rate limit específico
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

// Rotas autenticadas com rate limit geral
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('clients', ClientController::class);
    
    // Criação de clientes com limite adicional
    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('throttle:create-client');
});
```

### 📝 Resposta quando limite é excedido:

```json
{
  "message": "Muitas requisições. Tente novamente em alguns instantes.",
  "retry_after": 60
}
```

**HTTP Status:** `429 Too Many Requests`

**Headers retornados:**
- `X-RateLimit-Limit`: Limite total
- `X-RateLimit-Remaining`: Requisições restantes
- `Retry-After`: Segundos até poder tentar novamente

---

## 2. Sanitização de Inputs (Proteção contra XSS)

### 🛡️ Middleware de Sanitização:

**Arquivo:** `app/Http/Middleware/SanitizeInput.php`

**Funcionalidades:**
- Remove tags HTML/JavaScript (`<script>`, `<iframe>`, etc.)
- Remove espaços extras no início/fim
- Converte caracteres especiais (`<`, `>`, `&`, `"`, `'`)
- Previne ataques XSS (Cross-Site Scripting)

### ⚠️ Campos Excluídos da Sanitização:

Por segurança, alguns campos **NÃO** são sanitizados:
- `password`
- `password_confirmation`
- `current_password`

**Motivo:** Senhas podem conter caracteres especiais válidos que não devem ser alterados.

### 📋 Exemplo de Sanitização:

**Input malicioso:**
```json
{
  "name": "<script>alert('XSS')</script>João Silva",
  "email": "joao@example.com  "
}
```

**Output sanitizado:**
```json
{
  "name": "João Silva",
  "email": "joao@example.com"
}
```

---

## 3. Logs de Auditoria (Rastreabilidade Completa)

### 📁 Sistema de Auditoria:

Todos os eventos de criação, atualização e exclusão de clientes são registrados automaticamente.

**Arquivo de log:** `storage/logs/audit.log` (rotação diária, mantido por 90 dias)

### 🔍 Eventos Auditados:

| Evento | Nível | Dados Registrados |
|--------|-------|-------------------|
| Cliente Criado | `INFO` | ID, email, CPF, IP, user_agent, usuário autenticado |
| Cliente Atualizado | `INFO` | ID, alterações, valores originais, IP, usuário |
| Cliente Deletado | `WARNING` | ID, email, CPF, IP, usuário |
| Cliente Restaurado | `INFO` | ID, email, IP, usuário |
| Cliente Deletado Permanentemente | `ERROR` | ID, email, CPF, IP, usuário |

### 📝 Implementação:

**Observer:** `app/Observers/ClientObserver.php`

```php
public function created(Client $client): void
{
    Log::channel('audit')->info('Cliente criado', [
        'client_id' => $client->id,
        'email' => $client->email,
        'cpf' => $client->cpf,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'authenticated_user' => auth()->id(),
        'timestamp' => now()->toDateTimeString(),
    ]);
}
```

**Registro automático:** `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    Client::observe(ClientObserver::class);
    // ...
}
```

### 📊 Exemplo de Log de Auditoria:

```
[2024-01-29 15:30:45] audit.INFO: Cliente criado {"client_id":15,"email":"maria@example.com","cpf":"98765432100","ip":"192.168.1.100","user_agent":"PostmanRuntime/7.26.8","authenticated_user":1,"timestamp":"2024-01-29 15:30:45"}

[2024-01-29 15:35:12] audit.INFO: Cliente atualizado {"client_id":15,"changes":{"name":"Maria Santos Silva","updated_at":"2024-01-29 15:35:12"},"original":{"name":"Maria Santos","updated_at":"2024-01-29 15:30:45"},"ip":"192.168.1.100","authenticated_user":1,"timestamp":"2024-01-29 15:35:12"}

[2024-01-29 15:40:00] audit.WARNING: Cliente deletado {"client_id":15,"email":"maria@example.com","cpf":"98765432100","ip":"192.168.1.100","authenticated_user":1,"timestamp":"2024-01-29 15:40:00"}
```

---

## 4. Segurança Adicional Implementada

### ✅ Outras Proteções:

1. **Autenticação Laravel Sanctum:**
   - Tokens seguros via Bearer token
   - Expiração automática de tokens
   - Revogação de tokens no logout

2. **Validação de CPF:**
   - Algoritmo de validação de dígitos verificadores
   - Previne CPFs inválidos ou sequenciais
   - Unicidade garantida no banco de dados

3. **Tratamento de Exceções:**
   - Respostas padronizadas em JSON
   - Mensagens de erro sem exposição de detalhes internos
   - Códigos HTTP corretos (401, 404, 422, 429, 500)

4. **CORS Configurado:**
   - Proteção contra requisições de origens não autorizadas
   - Headers de segurança configurados

---

## 5. Visualização de Logs de Auditoria

### Ver logs em tempo real:

```bash
# Todos os logs de auditoria
docker compose exec app tail -f storage/logs/audit.log

# Apenas criações
docker compose exec app grep "Cliente criado" storage/logs/audit.log

# Apenas exclusões (incluindo permanentes)
docker compose exec app grep "deletado" storage/logs/audit.log

# Ações de um usuário específico
docker compose exec app grep "authenticated_user\":1" storage/logs/audit.log

# Ações de um IP específico
docker compose exec app grep "192.168.1.100" storage/logs/audit.log
```

### Rotação de logs:

- Arquivos são rotacionados diariamente
- Logs são mantidos por **90 dias**
- Arquivos antigos: `audit-2024-01-28.log`, `audit-2024-01-27.log`, etc.

---

## 6. Testes de Segurança

### Testar Rate Limiting (Login):

```bash
# Faça 6 requisições rápidas (5 permitidas + 1 bloqueada)
for i in {1..6}; do
  curl -X POST http://localhost:8000/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"wrong"}' && echo ""
done

# A 6ª requisição retornará HTTP 429
```

### Testar Sanitização:

```bash
curl -X POST http://localhost:8000/api/clients \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert(\"XSS\")</script>João",
    "email": "joao@example.com",
    "cpf": "12345678901",
    "password": "password123",
    "birth_date": "1990-01-01",
    "phone": "11987654321"
  }'

# O nome será salvo sem as tags: "João"
```

### Verificar Logs de Auditoria:

```bash
# Crie, atualize e delete um cliente
# Depois verifique os logs:
docker compose exec app cat storage/logs/audit.log | tail -20
```

---

## 7. Recomendações Adicionais

### 🔐 Para Produção:

1. **Variáveis de Ambiente:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   LOG_LEVEL=warning
   ```

2. **HTTPS Obrigatório:**
   - Configure certificado SSL/TLS
   - Force redirecionamento HTTP → HTTPS

3. **Autenticação de 2 Fatores:**
   - Considere adicionar 2FA para usuários críticos

4. **Monitoramento:**
   - Configure alertas para múltiplos erros 429
   - Monitor logs de auditoria para atividades suspeitas

5. **Backup de Logs:**
   - Configure backup automático de `storage/logs/audit.log`
   - Armazene em local seguro e imutável

---

## 📊 Resumo de Segurança

| Recurso | Status | Arquivo |
|---------|--------|---------|
| Rate Limiting | ✅ | `app/Providers/AppServiceProvider.php` |
| Sanitização XSS | ✅ | `app/Http/Middleware/SanitizeInput.php` |
| Logs de Auditoria | ✅ | `app/Observers/ClientObserver.php` |
| Validação de CPF | ✅ | `app/Rules/ValidCpf.php` |
| Autenticação Token | ✅ | Laravel Sanctum |
| Tratamento de Erros | ✅ | `bootstrap/app.php` |

---

**✅ Sistema seguro e auditável implementado com sucesso!**

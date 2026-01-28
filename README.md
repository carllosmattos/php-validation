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

### A aplicação estará disponível em:
http://localhost:8000

### Tecnologias utilizadas

PHP 8.4 (Laravel)

MySQL

Nginx

Docker / Docker Compose

### Observações
- O projeto utiliza volumes montados para desenvolvimento. Em ambientes Windows, pode ser necessário garantir permissões de escrita nos diretórios storage e bootstrap/cache.
- A aplicação foi desenvolvida de forma monolítica, com API REST disponível para os principais recursos, permitindo fácil desacoplamento do frontend no futuro.


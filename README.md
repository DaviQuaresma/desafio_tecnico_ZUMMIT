# Travel Orders API - Microsserviço de Pedidos de Viagem

Microsserviço em Laravel para gerenciamento de pedidos de viagem corporativa.

## Requisitos

- Docker
- Docker Compose

## Stack

- **PHP 8.2** com Apache
- **Laravel 12.x**
- **MySQL 8.0**
- **JWT Auth** (tymon/jwt-auth)

## Instalação e Execução

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd desafio_tecnico_ZUMMIT
```

### 2. Copie o arquivo de ambiente

```bash
cp .env.example .env
```

### 3. Suba os containers

```bash
docker-compose up -d --build
```

### 4. Instale as dependências (primeira vez)

```bash
docker-compose exec app composer install
```

### 5. Gere a chave da aplicação

```bash
docker-compose exec app php artisan key:generate
```

### 6. Gere a chave JWT

```bash
docker-compose exec app php artisan jwt:secret
```

### 7. Execute as migrations

```bash
docker-compose exec app php artisan migrate
```

## Executar Testes

### Todos os testes
```bash
docker-compose exec app php artisan test
```

### Apenas testes de autenticação
```bash
docker-compose exec app php artisan test --filter=AuthTest
```

### Apenas testes de pedidos de viagem
```bash
docker-compose exec app php artisan test --filter=TravelOrderTest
```

## API Endpoints

### Autenticação

| Método | Endpoint           | Descrição                    | Auth |
|--------|-------------------|------------------------------|------|
| POST   | /api/auth/register | Registrar novo usuário       | Não  |
| POST   | /api/auth/login    | Login (retorna JWT token)    | Não  |
| POST   | /api/auth/logout   | Logout (invalida token)      | Sim  |
| POST   | /api/auth/refresh  | Atualizar token              | Sim  |
| GET    | /api/auth/me       | Perfil do usuário autenticado| Sim  |

### Pedidos de Viagem

| Método | Endpoint                      | Descrição                    | Auth |
|--------|------------------------------|------------------------------|------|
| GET    | /api/travel-orders           | Listar pedidos do usuário    | Sim  |
| POST   | /api/travel-orders           | Criar novo pedido            | Sim  |
| GET    | /api/travel-orders/{id}      | Visualizar pedido específico | Sim  |
| PATCH  | /api/travel-orders/{id}/status | Atualizar status (aprovar/cancelar) | Sim |
| POST   | /api/travel-orders/{id}/cancel | Cancelar pedido             | Sim  |

### Filtros para Listagem

- `status` - Filtrar por status (requested, approved, canceled)
- `destination` - Filtrar por destino (busca parcial)
- `start_date` e `end_date` - Filtrar por período de viagem
- `per_page` - Itens por página (padrão: 15)

Exemplo: `GET /api/travel-orders?status=approved&destination=Paulo&start_date=2026-04-01&end_date=2026-04-30`

## Regras de Negócio

1. **Usuário só vê seus próprios pedidos** - Cada usuário tem acesso apenas aos seus pedidos de viagem
2. **Usuário não pode aprovar/cancelar seu próprio pedido** - Apenas outros usuários podem alterar o status
3. **Dono pode cancelar apenas pedidos "solicitados"** - Se o status já for "aprovado", o dono não pode cancelar
4. **Outros usuários podem cancelar pedidos aprovados** - Administradores/gestores podem cancelar
5. **Notificações** - O solicitante recebe notificação quando o pedido é aprovado ou cancelado

## Autenticação JWT

Todas as rotas protegidas requerem o header de autorização:

```
Authorization: Bearer <seu_token_jwt>
```

O token é retornado após login ou registro e expira após o tempo configurado (padrão: 60 minutos).

## Acessos

| Serviço      | URL                          |
|--------------|------------------------------|
| Aplicação    | http://localhost:8080        |
| MySQL        | localhost:3306               |

### Credenciais do Banco de Dados

| Campo    | Valor         |
|----------|---------------|
| Host     | mysql         |
| Porta    | 3306          |
| Database | travel_orders |
| Usuário  | laravel       |
| Senha    | laravel       |
| Root     | root          |

## Comandos Úteis

### Acessar o container da aplicação
```bash
docker-compose exec app bash
```

### Executar comandos Artisan
```bash
docker-compose exec app php artisan <comando>
```

### Ver logs da aplicação
```bash
docker-compose logs -f app
```

### Parar os containers
```bash
docker-compose down
```

### Parar e remover volumes (limpa o banco)
```bash
docker-compose down -v
```

### Rebuildar os containers
```bash
docker-compose up -d --build
```

## Estrutura do Projeto

```
├── app/
│   ├── Enums/              # Enums (TravelOrderStatus)
│   ├── Http/
│   │   ├── Controllers/    # Controllers da API
│   │   ├── Requests/       # Form Requests (validação)
│   │   └── Resources/      # API Resources (formatação)
│   ├── Models/             # Models Eloquent
│   ├── Notifications/      # Notificações (email/database)
│   └── Services/           # Services (lógica de negócio)
├── database/
│   ├── factories/          # Factories para testes
│   └── migrations/         # Migrations do banco
├── docker/                 # Configurações Docker
├── routes/api.php          # Rotas da API
├── tests/
│   ├── Feature/            # Testes de integração
│   └── Unit/               # Testes unitários
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Licença

Este projeto é desenvolvido para fins de avaliação técnica.

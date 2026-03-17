# Travel Orders API - Microsserviço de Pedidos de Viagem

Microsserviço em Laravel para gerenciamento de pedidos de viagem corporativa.

## Requisitos

- Docker
- Docker Compose

## Stack

- **PHP 8.2** com Apache
- **Laravel 12.x**
- **MySQL 8.0**

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

### 6. Execute as migrations

```bash
docker-compose exec app php artisan migrate
```

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
├── app/                    # Código da aplicação Laravel
├── bootstrap/              # Arquivos de inicialização
├── config/                 # Arquivos de configuração
├── database/               # Migrations e seeders
├── docker/                 # Configurações Docker
│   ├── apache/             # Configuração do Apache
│   ├── mysql/              # Scripts de inicialização MySQL
│   └── php/                # Configurações PHP
├── public/                 # Arquivos públicos
├── resources/              # Views e assets
├── routes/                 # Definição de rotas
├── storage/                # Arquivos de storage
├── tests/                  # Testes automatizados
├── .env                    # Variáveis de ambiente
├── docker-compose.yml      # Configuração Docker Compose
├── Dockerfile              # Build da imagem Docker
└── README.md               # Este arquivo
```

## Licença

Este projeto é desenvolvido para fins de avaliação técnica.

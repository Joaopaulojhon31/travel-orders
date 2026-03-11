# Travel Orders API

API REST para gerenciamento de pedidos de viagem corporativa.

## Tecnologias
- Laravel 12
- MySQL 8
- JWT Authentication (`tymon/jwt-auth`)
- Docker + Docker Compose

## Requisitos
- Docker
- Docker Compose

## Como executar

### 1. Clone o repositório
```bash
git clone https://github.com/Joaopaulojhon31/travel-orders.git
cd travel-orders-api
```

### 2. Configure o ambiente
```bash
cp .env.example .env
```

Edite o `.env` com as variáveis abaixo. Atenção: o `DB_HOST` deve ser `db` para funcionar dentro do Docker:
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=travel_orders
DB_USERNAME=travel_user
DB_PASSWORD=travel_pass
```

### 3. Suba os containers
```bash
docker compose up -d --build
```

### 4. Instale as dependências e configure
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret  ##(yes)
docker compose exec app php artisan migrate --seed
```

### 5. Acesse a API
```
http://localhost:8000/api
```

## Executar testes
```bash
# Com Docker
docker compose exec app php artisan test

# Localmente (requer PHP e Composer instalados)
php artisan test
```

> Os testes utilizam SQLite em memória, não afetando o banco de dados principal.

## Usuários padrão (criados pelo seed)

| E-mail | Senha | Admin |
|--------|-------|-------|
| admin@admin.com | admin1234 | ✅ |
| joao@email.com | 12345678 | ❌ |

## Endpoints

### Autenticação
| Método | Rota | Descrição | Auth |
|--------|------|-----------|------|
| POST | /api/auth/register | Cadastrar usuário | ❌ |
| POST | /api/auth/login | Login | ❌ |
| POST | /api/auth/logout | Logout | ✅ |
| GET | /api/auth/me | Usuário autenticado | ✅ |

### Pedidos de Viagem
| Método | Rota | Descrição | Admin |
|--------|------|-----------|-------|
| GET | /api/travel-orders | Listar pedidos | ❌ |
| POST | /api/travel-orders | Criar pedido | ❌ |
| GET | /api/travel-orders/{id} | Ver pedido | ❌ |
| PATCH | /api/travel-orders/{id}/status | Atualizar status | ✅ |
| DELETE | /api/travel-orders/{id} | Deletar pedido | ❌ |

### Notificações
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | /api/notifications | Listar notificações |
| PATCH | /api/notifications/{id}/read | Marcar como lida |

## Filtros disponíveis
```
GET /api/travel-orders?status=aprovado
GET /api/travel-orders?status=cancelado
GET /api/travel-orders?destination=São Paulo
GET /api/travel-orders?date_from=2026-01-01&date_to=2026-12-31
GET /api/travel-orders?status=aprovado&destination=Rio&date_from=2026-01-01
```

## Regras de negócio

- Cada usuário vê e gerencia apenas seus próprios pedidos
- Administradores visualizam todos os pedidos do sistema
- Somente administradores podem aprovar ou cancelar pedidos
- Pedidos com status `aprovado` não podem ser cancelados
- Ao aprovar ou cancelar um pedido, uma notificação é gerada para o solicitante

## Decisões técnicas

### Notificações
As notificações utilizam o driver `database` do Laravel. Sempre que um pedido é aprovado ou cancelado, uma notificação é registrada no banco e pode ser visualizada pelo usuário no frontend, tanto no sino do dashboard quanto na página de detalhes do pedido.

### Autenticação
A API utiliza JWT via pacote `tymon/jwt-auth`. O token deve ser enviado no header de todas as rotas protegidas:
```
Authorization: Bearer {token}
```


## Estrutura do projeto
```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php         # Login, registro, logout
│   └── TravelOrderController.php  # CRUD de pedidos e atualização de status
├── Models/
│   ├── User.php                   # Model de usuário com JWT
│   └── TravelOrder.php            # Model de pedido de viagem
└── Notifications/
    └── TravelOrderStatusChanged.php # Notificação de mudança de status

database/
├── factories/
│   └── TravelOrderFactory.php     # Factory para testes
├── migrations/                    # Migrations das tabelas
└── seeders/
    └── UserSeeder.php             # Usuários padrão para testes

routes/
└── api.php                        # Rotas da API

tests/
└── Feature/
    ├── AuthTest.php               # Testes de autenticação
    └── TravelOrderTest.php        # Testes de pedidos de viagem
```

## Regras de negócio

- Cada usuário vê e gerencia apenas seus próprios pedidos
- Administradores visualizam todos os pedidos do sistema
- Somente administradores podem aprovar ou cancelar pedidos
- Apenas pedidos com status `solicitado` podem ter o status alterado
- Pedidos com status `aprovado` ou `cancelado` não podem ser atualizados
- Ao aprovar ou cancelar um pedido, uma notificação é gerada para o solicitante

## Cobertura de testes

| Teste | Descrição |
|-------|-----------|
| `test_user_can_register` | Cadastro de novo usuário |
| `test_user_can_login` | Login com credenciais válidas |
| `test_login_fails_with_wrong_password` | Bloqueio com senha incorreta |
| `test_user_can_create_travel_order` | Criação de pedido |
| `test_user_can_list_own_orders` | Isolamento de pedidos por usuário |
| `test_admin_can_update_status` | Admin aprova pedido |
| `test_regular_user_cannot_update_status` | Usuário comum bloqueado |
| `test_cannot_update_status_of_non_pending_order` | Bloqueio de pedido aprovado |
| `test_cannot_approve_cancelled_order` | Bloqueio de pedido cancelado |
| `test_regular_user_cannot_approve_order` | Usuário não pode aprovar |
| `test_regular_user_cannot_cancel_order` | Usuário não pode cancelar |

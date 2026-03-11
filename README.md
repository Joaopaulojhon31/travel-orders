# Travel Orders

Aplicação Full Stack para gerenciamento de pedidos de viagem corporativa.

O sistema é composto por:

- **Backend:** API REST em Laravel com autenticação JWT
- **Frontend:** Interface em Vue.js
- **Infraestrutura:** Docker e Docker Compose

## Estrutura do projeto
```
travel-orders/
├── backend/    # API Laravel
├── frontend/   # Aplicação Vue.js
└── README.md
```

## Como executar o projeto

### 1. Clonar o repositório
```bash
git clone https://github.com/Joaopaulojhon31/travel-orders.git
cd travel-orders
```

### 2. Siga as instruções de cada serviço

- Backend: [`backend/README.md`](./backend/README.md)
- Frontend: [`frontend/README.md`](./frontend/README.md)

## Serviços

| Serviço | URL |
|---------|-----|
| API | http://localhost:8000 |
| Frontend | http://localhost:5173 |

## Usuários padrão

Criados automaticamente via seed do backend:

| E-mail | Senha | Admin |
|--------|-------|-------|
| admin@admin.com | admin1234 | ✅ |
| joao@email.com | 12345678 | ❌ |

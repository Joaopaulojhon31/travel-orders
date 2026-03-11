# Travel Orders Frontend

Interface Vue.js para o sistema de gerenciamento de pedidos de viagem corporativa.

## Tecnologias
- Vue.js 3
- Vue Router
- Pinia
- Axios
- Bootstrap 5
- Vue Toastification
- Vite
- Docker

## Requisitos

### Local
- Node.js 22+

### Docker
- Docker
- Docker Compose

## Como executar

### Opção 1 — Local
```bash
npm install
npm run dev
```

Acesse: http://localhost:5173

### Opção 2 — Docker
```bash
docker compose up -d --build
```

Acesse: http://localhost:5173

> O backend deve estar rodando em `http://localhost:8000` antes de iniciar o frontend.

## Configuração da API

A URL base da API está configurada em `src/api/axios.js`:
```javascript
baseURL: 'http://localhost:8000/api'
```

Se o backend estiver em outro endereço, atualize essa variável.

## Funcionalidades

- Cadastro e login de usuário com JWT
- Rotas protegidas — redireciona para login se não autenticado
- Dashboard com listagem de pedidos em tabela
- Filtros por status, destino e período de datas
- Sino de notificações com contador de não lidas
- Formulário de criação de pedido de viagem
- Página de detalhes do pedido
- Aprovação e cancelamento de pedidos (apenas admin)
- Notificações exibidas no dashboard e na página de detalhes
- Loading spinner em todas as operações assíncronas
- Mensagens de sucesso e erro via toast

## Perfis de acesso

| Perfil | Permissões |
|--------|-----------|
| Usuário comum | Criar, visualizar e listar seus próprios pedidos |
| Administrador | Visualizar todos os pedidos, aprovar e cancelar |

## Usuários padrão (criados pelo seed do backend)

| E-mail | Senha | Admin |
|--------|-------|-------|
| admin@admin.com | admin1234 | ✅ |
| joao@email.com | 12345678 | ❌ |

## Estrutura do projeto
```
src/
├── api/
│   └── axios.js          # Configuração do Axios e interceptors JWT
├── components/
│   └── ConfirmModal.vue  # Modal de confirmação reutilizável
├── router/
│   └── index.js          # Rotas e guards de autenticação
├── stores/
│   └── auth.js           # Store de autenticação com Pinia
└── views/
    ├── LoginView.vue      # Tela de login
    ├── RegisterView.vue   # Tela de cadastro
    ├── DashboardView.vue  # Listagem de pedidos com filtros e notificações
    ├── NewOrderView.vue   # Formulário de criação de pedido
    └── OrderDetailView.vue # Detalhes e atualização de status
```

## Observações técnicas

- O token JWT é armazenado no `localStorage` e enviado automaticamente em todas as requisições via interceptor do Axios
- Sessões expiradas redirecionam automaticamente para o login
- O debounce no filtro de destino evita requisições excessivas durante a digitação
- O modal de confirmação é um componente reutilizado nas telas de dashboard e detalhes do pedido
# Sistema de Cadastro de Clientes - Fametro

Sistema completo de cadastro de clientes com backend Laravel e frontend Angular.

## Pré-requisitos

- Docker
- Docker Compose
- NPM (para rodar frontend sem Docker)

## Estrutura

```
├── backend/   # Laravel + JWT + SQL Server
├── frontend/  # Angular + TypeScript
└── docker-compose.yml
```

## Configuração Inicial

### 1. Copiar variáveis de ambiente

```bash
cp backend/.env.example backend/.env
```

### 2. Gerar JWT Secret (se necessário)

```bash
docker compose exec backend php artisan jwt:secret
```

### 3. Subir containers

```bash
docker compose up -d
```

### 4. Rodar migrations e seeders

```bash
docker compose exec backend php artisan migrate:fresh --seed
```

## Acesso

- **Frontend**: http://localhost:4200
- **Backend API**: http://localhost:8000/api

### Credenciais de teste

- **Email**: `teste@famtetro.edu.br`
- **Senha**: `senha123`

## Comandos úteis

```bash
# Ver logs
docker compose logs -f

# Logs específicos
docker compose logs backend -f
docker compose logs frontend -f

# Restart
docker compose restart

# Parar
docker compose down

# Rodar testes backend
docker compose exec backend php artisan test

# Acessar container backend
docker compose exec backend bash
```

## Endpoints API

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/login` | Login |
| GET | `/api/clientes` | Listar clientes |
| POST | `/api/clientes` | Criar cliente |
| PUT | `/api/clientes/{id}` | Atualizar cliente |
| DELETE | `/api/clientes/{id}` | Excluir cliente |
| GET | `/api/cep/{cep}` | Consultar CEP |

## Tecnologias

- **Backend**: Laravel 13, PHP 8.3, SQL Server, JWT
- **Frontend**: Angular 21, TypeScript
- **Testes**: Pest PHP

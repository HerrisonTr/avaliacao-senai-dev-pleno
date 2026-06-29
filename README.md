# Avaliacao SENAI

Projeto com backend em Laravel e frontend separado, ambos executados com Docker.

## Stack

- PHP 8.4 (FPM)
- Laravel 13
- MySQL 8
- Nginx
- Frontend estatico com HTML, Bootstrap e JavaScript

## Estrutura

- `backend/`: API Laravel
- `frontend/`: interface separada
- `docker/`: configuracoes de PHP e Nginx
- `docker-compose.yml`: orquestracao dos containers

## Portas

- `8080`: API Laravel
- `8090`: frontend
- `3306`: MySQL

## Como rodar com Docker

### 1. Subir os containers

```bash
docker compose up -d --build
```

### 2. Criar o arquivo de ambiente do backend

```bash
cp backend/.env.example backend/.env
```

### 3. Configurar o banco no backend

No arquivo `backend/.env`, ajuste os dados abaixo:

```env
APP_NAME="Avaliacao SENAI"
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app_senai
DB_USERNAME=herrison
DB_PASSWORD=123456
```

### 4. Instalar dependencias do backend

```bash
docker compose exec app composer install
```

### 5. Gerar a chave da aplicacao

```bash
docker compose exec app php artisan key:generate
```

### 6. Rodar as migrations

```bash
docker compose exec app php artisan migrate
```

### 7. Acessar os ambientes

```text
API: http://localhost:8080
Frontend: http://localhost:8090
```

## Comandos uteis

Subir containers:

```bash
docker compose up -d
```

Parar containers:

```bash
docker compose down
```

Ver logs:

```bash
docker compose logs -f
```

Entrar no container PHP:

```bash
docker compose exec app bash
```

Rodar testes do backend:

```bash
docker compose exec app php artisan test
```

Rodar seeders:

```bash
docker compose exec app php artisan db:seed
```

## Frontend

O frontend agora esta em `frontend/` e e servido por um container Nginx proprio.

Arquivos iniciais:

- `frontend/index.html`
- `frontend/assets/css/styles.css`
- `frontend/assets/js/app.js`

## Primeira execucao recomendada

```bash
docker compose up -d --build
cp backend/.env.example backend/.env
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Antes de rodar a migration, ajuste o `backend/.env` para usar MySQL com as credenciais mostradas acima.

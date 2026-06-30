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

### 1. Criar o arquivo de ambiente do Docker Compose

O Docker Compose usa um arquivo `.env` na raiz do projeto para definir o usuario do container `app`. Isso evita que o Laravel gere arquivos com permissões incorretas no host.

```bash
cp .env.example .env
```

Depois, ajuste os valores de `APP_UID` e `APP_GID` no arquivo `.env` da raiz com o UID e o GID do seu usuario no host.

Para descobrir esses valores, use:

```bash
id -u
id -g
```

### 2. Subir os containers

```bash
docker compose up -d --build
```

### 3. Criar o arquivo de ambiente do backend

```bash
cp backend/.env.example backend/.env
```

### 4. Configurar o banco no backend

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

### 5. Instalar dependencias do backend

```bash
docker compose exec app composer install
```

### 6. Gerar a chave da aplicacao

```bash
docker compose exec app php artisan key:generate
```

### 7. Rodar as migrations

```bash
docker compose exec app php artisan migrate
```

### 8. Acessar os ambientes

```text
API: http://localhost:8080
Frontend: http://localhost:8090
```

### 9. Popular o banco com os dados iniciais

```bash
docker compose exec app php artisan db:seed
```

### 10. Primeiro acesso

Após rodar a seed, acesse o frontend em `http://localhost:8090` e entre com um dos usuários abaixo:

- Administrador
  - E-mail: `admin@admin.com`
  - Senha: `123qwe!!`
- Atendente
  - E-mail: `atendente@atendente.com`
  - Senha: `123qwe!!`

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

## Ajustando permissões

O container `app` usa os valores de `APP_UID` e `APP_GID` definidos no arquivo `.env` da raiz do projeto. Com isso, o comportamento esperado e que nao seja necessario corrigir permissões manualmente a cada execução.

Se ainda houver erro de permissão no Laravel, execute os comandos abaixo dentro do container `app`:

```bash
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage
```

## Erro de permissão no VS Code

Se o workspace ficar com arquivos sem permissão para seu usuário no host, execute:

```bash
sudo chown -R seu_usuario:seu_usuario caminho_do_projeto
sudo chown -R seu_usuario:seu_usuario backend/storage backend/bootstrap/cache
sudo chmod -R 775 backend/storage backend/bootstrap/cache
```

## Frontend

O frontend agora esta em `frontend/` e e servido por um container Nginx proprio.

Arquivos iniciais:

- `frontend/index.html`
- `frontend/assets/css/styles.css`
- `frontend/assets/js/app.js`

## Primeira execucao recomendada

```bash
cp .env.example .env
docker compose up -d --build
cp backend/.env.example backend/.env
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Antes de subir os containers, ajuste o arquivo `.env` da raiz com o `APP_UID` e o `APP_GID` do seu usuario. Antes de rodar a migration, ajuste o `backend/.env` para usar MySQL com as credenciais mostradas acima. Depois da seed, o acesso ao sistema pode ser feito com os usuários `admin@admin.com` ou `atendente@atendente.com`, ambos com a senha `123qwe!!`.

# Avaliacao SENAI

Projeto base em Laravel com ambiente Docker para PHP, Nginx e MySQL.

## Stack

- PHP 8.4 (FPM)
- Laravel 13
- MySQL 8
- Nginx
- Vite
- Tailwind CSS 4

## Estrutura

- `docker-compose.yml`: orquestra os containers da aplicacao
- `docker/`: configuracoes de PHP e Nginx
- `src/`: codigo-fonte do Laravel

## Como rodar com Docker

### 1. Subir os containers

Na raiz do projeto, execute:

```bash
docker compose up -d --build
```

Isso sobe:

- `app`: container PHP-FPM
- `nginx`: servidor web na porta `8080`
- `mysql`: banco de dados MySQL na porta `3306`

### 2. Criar o arquivo de ambiente do Laravel

```bash
cp src/.env.example src/.env
```

### 3. Ajustar o banco no `.env`

O projeto Laravel vem configurado por padrao para `sqlite`, mas o ambiente Docker sobe um `mysql`.

No arquivo `src/.env`, ajuste para:

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

### 4. Instalar dependencias PHP

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

### 7. Acessar o sistema

Abra no navegador:

```text
http://localhost:8080
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

Rodar testes:

```bash
docker compose exec app php artisan test
```

Rodar seeders:

```bash
docker compose exec app php artisan db:seed
```

## Primeira execucao recomendada

Se quiser fazer o setup inicial em sequencia:

```bash
docker compose up -d --build
cp src/.env.example src/.env
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Antes de rodar a migration, ajuste o `src/.env` para usar MySQL com as credenciais mostradas acima. Depois disso, acesse `http://localhost:8080`.

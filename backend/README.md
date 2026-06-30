# Backend - Avaliação SENAI

API desenvolvida em Laravel para autenticação, controle de acesso e gestão dos módulos do sistema.

## Stack

- PHP 8.4
- Laravel 13
- Laravel Sanctum
- Spatie Permission
- MySQL 8

## Principais recursos

- Autenticação via API com Sanctum
- Controle de perfis e permissões
- Gestão de usuários
- Gestão de disponibilidades de atendentes
- Catálogo de serviços
- Gestão de agendamentos
- Seeders iniciais para perfis, permissões, usuários e serviços

## Estrutura principal

- `app/Http/Controllers/Api`: controllers da API
- `app/Http/Requests/Api`: validações por módulo
- `app/Services`: regras de negócio mais complexas
- `routes/api.php`: rotas da API
- `database/migrations`: estrutura do banco
- `database/seeders`: dados iniciais

## Configuração

### 1. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

### 2. Ajustar variáveis principais

Exemplo:

```env
APP_NAME="Avaliacao SENAI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_TIMEZONE=America/Sao_Paulo

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app_senai
DB_USERNAME=herrison
DB_PASSWORD=123456
```

### 3. Instalar dependências

```bash
composer install
```

### 4. Gerar chave da aplicação

```bash
php artisan key:generate
```

### 5. Rodar migrations e seeders

```bash
php artisan migrate
php artisan db:seed
```

## Rodando com Docker

Se estiver usando a estrutura principal do projeto:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

## Autenticação

A autenticação da API é feita com Laravel Sanctum.

Fluxo principal:

- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

A rota `me` retorna os dados do usuário autenticado, incluindo perfil e permissões.

## Módulos da API

Rotas principais disponíveis em `routes/api.php`:

- autenticação
- usuários
- disponibilidades de atendentes
- serviços
- agendamentos

## Comandos úteis

Rodar testes:

```bash
php artisan test
```

Rodar formatter:

```bash
./vendor/bin/pint
```

Limpar caches:

```bash
php artisan optimize:clear
```

Rodar seeders novamente:

```bash
php artisan db:seed
```

## Permissões de escrita

Se houver erro de permissão no Laravel:

```bash
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage

chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage
```

Se estiver usando o projeto pelo host e o VS Code perder acesso aos arquivos:

```bash
sudo chown -R herrison:herrison /home/herrison/projetos/avaliacao-senai/
sudo chown -R herrison:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

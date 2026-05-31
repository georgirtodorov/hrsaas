# HR SaaS

SaaS HR апликация за човешки ресурси. Laravel 13 + React 19 + Inertia.js v3 + Filament v5.

## Quick Start

```bash
# Стартиране на Docker контейнерите
docker compose up -d

# Инсталиране на PHP зависимости
docker exec hrapp-laravel.test-1 composer install

# Инсталиране на JS зависимости и build
docker exec hrapp-laravel.test-1 npm install
docker exec hrapp-laravel.test-1 npm run build

# Пускане на миграциите
docker exec hrapp-laravel.test-1 php artisan migrate

# (Ако стартирате за първи път) Създайте admin потребител
docker exec hrapp-laravel.test-1 php artisan tinker --execute '
User::factory()->create([
    "name" => "Admin",
    "email" => "admin@hrapp.app",
    "password" => bcrypt("password"),
    "is_admin" => true,
]);
'
```

## Достъп

| Ресурс | URL | Достъп |
|--------|-----|--------|
| App (frontend) | http://localhost | Всички регистрирани |
| Admin Panel | http://localhost/admin | Само `is_admin = true` |
| Login | http://localhost/login | Всички |
| Register | http://localhost/register | Всички |

## Default потребител

| Email | Password | Роля |
|-------|----------|------|
| admin@hrapp.app | password | System admin (`is_admin: true`) |

## Admin Panel

Достъпен на `/admin` само за потребители с `is_admin = true`.

### Текущи ресурси:
- **Teams (Companies)** — CRUD на компании/tenants, активиране/спиране

## Структура

```
app/
├── Filament/
│   └── Resources/
│       └── Teams/
│           ├── TeamResource.php
│           └── Pages/
│               └── ManageTeams.php
├── Http/
│   └── Middleware/
│       ├── EnsureUserIsAdmin.php      # Admin достъп
│       └── EnsureTeamMembership.php   # Team достъп
├── Models/
│   ├── User.php
│   ├── Team.php
│   ├── Membership.php
│   └── TeamInvitation.php
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php     # /admin конфигурация
```

## Docker

```bash
# Старт
docker compose up -d

# Спиране
docker compose down

# Artisan (в контейнера)
docker exec hrapp-laravel.test-1 php artisan [command]

# Composer (в контейнера)
docker exec hrapp-laravel.test-1 composer require [package]

# NPM build (в контейнера)
docker exec hrapp-laravel.test-1 npm run build

# Tinker
docker exec hrapp-laravel.test-1 php artisan tinker --execute 'Your::code();'

# Logs (Laravel Pail)
docker exec hrapp-laravel.test-1 php artisan pail
```

## Environment

- PHP 8.5
- MySQL 8.4 (docker)
- Node 22+ (docker)
- Redis (за queue/cache)

## План за развитие

Виж [.opencode/plans/PLAN.md](.opencode/plans/PLAN.md).

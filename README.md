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

# Пускане на миграциите + seed данни
docker exec hrapp-laravel.test-1 php artisan migrate:fresh --seed
```

## Достъп

| Ресурс | URL | Достъп |
|--------|-----|--------|
| App (frontend) | http://localhost | Всички регистрирани |
| Admin Panel | http://localhost/admin | Само `is_admin = true` |
| Company Panel | http://localhost/{team}/company | Членове на team-а |
| Login | http://localhost/login | Всички |
| Register | http://localhost/register | Всички |

## Default потребител

| Email | Password | Роля |
|-------|----------|------|
| admin@hrapp.app | password | System admin (`is_admin: true`) |
| test@hrapp.app | password | Regular — член на Acme Corp, Globex Inc, Initech |

## Admin Panel

Достъпен на `/admin` само за потребители с `is_admin = true`.

### Текущи ресурси:
- **Teams (Companies)** — CRUD на компании/tenants, активиране/спиране
- **Departments** — CRUD на отдели, филтър по компания
- **Users** — преглед на потребители, управление на team membership и HR запис

## Company Panel

Достъпен на `/{team:slug}/company` за членове на team-а. Админите (`is_admin = true`)
също имат достъп до всички company panels.

### Текущи ресурси:
- **Employees** — CRUD на служители (HR записи), scoped към текущия tenant
- **Departments** — CRUD на отдели, scoped към текущия tenant

### Процес на достъп:
1. Потребителят влиза през **Fortify `/login`** (единствен логин, двата панела нямат `->login()`)
2. Админ → `/admin`, Regular → `/{currentTeam}/company`
3. Company панелът проверява членство чрез `EnsureTeamMembership` middleware (с admin bypass)
4. Всички заявки минават през `CompanyAuthenticate` който redirect-ва към `/login`

## Структура

```
app/
├── Filament/
│   ├── Company/
│   │   └── Resources/
│   │       ├── DepartmentResource.php     # Company отдели
│   │       ├── EmployeeResource.php       # Company служители
│   │       └── Pages/
│   │           ├── CreateDepartment.php
│   │           ├── CreateEmployee.php
│   │           ├── EditDepartment.php
│   │           ├── EditEmployee.php
│   │           └── ListDepartments.php (или ListEmployees.php)
│   └── Resources/
│       ├── Teams/
│       │   ├── TeamResource.php
│       │   └── Pages/
│       │       └── ManageTeams.php
│       └── ... (Admin resources)
├── Http/
│   ├── Middleware/
│   │   ├── EnsureUserIsAdmin.php          # Admin достъп
│   │   ├── EnsureTeamMembership.php       # Team достъп + URL defaults
│   │   ├── CompanyAuthenticate.php        # Company panel auth → /login
│   │   └── AdminAuthenticate.php          # Admin panel auth → /login
│   └── Responses/
│       └── Concerns/
│           └── RedirectsToCurrentTeam.php  # Post-login redirect
├── Models/
│   ├── User.php
│   ├── Team.php
│   ├── Membership.php
│   ├── TeamInvitation.php
│   ├── Employee.php
│   └── Department.php
└── Providers/
    └── Filament/
        ├── AdminPanelProvider.php         # /admin конфигурация
        └── CompanyPanelProvider.php       # /{team}/company конфигурация
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

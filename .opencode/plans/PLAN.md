# HR SaaS — План за развитие

## Overview

SaaS HR апликация за човешки ресурси.

**MVP стратегия:** Една MySQL база, Team = компания (tenant).
След MVP ще преценим database-per-tenant.

---

## 1. Текущо състояние

- Laravel 13.x + React 19 + Inertia.js v3
- Laravel Fortify (auth, 2FA, passkeys, email verification)
- Teams система (Team, Membership, TeamInvitation)
- Tailwind CSS v4 (само build-time)
- Filament v5.6.6 — AdminPanel (`/admin`)
- ESLint + Prettier + TypeScript

---

## 2. Архитектура

### 2.1 Tenancy (MVP)

Една MySQL база. Team = компания (tenant).
Всички tenant данни имат `team_id` FK.
Потребителите (users) са глобални и членуват в няколко team-a.

### 2.2 Структура на папките

```
app/
├── Models/
│   ├── User.php, Team.php, Membership.php, TeamInvitation.php
│   ├── Employee.php, Department.php
│   └── (бъдещи: AbsenceType, LeaveRequest)
│
├── Filament/
│   ├── Company/
│   │   └── Resources/
│   │       ├── DepartmentResource.php    # (Company) отдели
│   │       └── EmployeeResource.php      # (Company) служители
│   └── Resources/
│       ├── Teams/         → TeamResource + MembersRelationManager
│       ├── Departments/   → DepartmentResource
│       └── Users/         → UserResource + TeamsRelationManager + EmployeeRelationManager
│
├── Policies/
│   └── TeamPolicy.php     (admins bypass)
│
├── Http/Middleware/
│   ├── EnsureUserIsAdmin.php
│   └── EnsureTeamMembership.php
│
└── Providers/Filament/
    ├── AdminPanelProvider.php
    └── CompanyPanelProvider.php
```

### 2.3 Panels

| Panel | Path | Достъп | Какво прави |
|-------|------|--------|-------------|
| **AdminPanel** | `/admin` | `is_admin = true` | Управление на компании, users, departments, employees |
| **CompanyPanel** | `/{team:slug}/company` | Членове на team-а | HR панел за компанията (отдели, служители) |

---

## 3. Database Schema

### 3.1 Съществуващи таблици

```sql
users:            id, name, email, password, is_admin, current_team_id, ...
teams:            id, name, slug, is_personal, is_active, deleted_at, ...
team_members:     team_id, user_id, role (Owner/Admin/Member)
team_invitations: id, team_id, email, role, ...
```

### 3.2 Нови (HR)

```sql
departments:      id, team_id FK, name, description, timestamps
                  UNIQUE(team_id, name)

employees:        id, team_id FK, user_id FK?,
                  department_id FK?, first_name, last_name, email,
                  phone?, job_title?, hire_date?, soft_deletes
                  UNIQUE(team_id, email)
```

---

## 4. Състояние на задачите

### Фаза 0 — Foundation

#### 0.A — Infrastructure ✅ **ЗАВЪРШЕНА**

| Стъпка | Статус |
|--------|--------|
| Docker + MySQL | ✅ |
| Laravel работи в container | ✅ |
| `.env` конфигурация | ✅ |

#### 0.B — Admin Domain

| Стъпка | Задача | Статус |
|--------|--------|--------|
| **0.B.1** | **Filament инсталация** | ✅ |
| 0.B.1.1 | `composer require filament/filament` | ✅ |
| 0.B.1.2 | `boost:install --skills` | ✅ |
| 0.B.1.3 | `filament:install --panels` (`/admin`) | ✅ |
| 0.B.1.4 | Theme + npm build | ✅ |
| 0.B.1.5 | `npx skills add filament-pro` | ✅ |
| **0.B.2** | **Auth за AdminPanel** | ✅ |
| 0.B.2.1 | `is_admin` migration | ✅ |
| 0.B.2.2 | `User::isAdmin()` | ✅ |
| 0.B.2.3 | `EnsureUserIsAdmin` middleware | ✅ |
| 0.B.2.4 | Middleware в AdminPanelProvider | ✅ |
| **0.B.3** | **Team CRUD** | ✅ |
| 0.B.3.1 | TeamResource (List, Create, Edit) | ✅ |
| 0.B.3.2 | Table: search, sort, toggle is_active | ✅ |
| 0.B.3.3 | Form: name, slug (auto), is_active | ✅ |
| 0.B.3.4 | `is_active` migration | ✅ |
| 0.B.3.5 | TeamPolicy — admins bypass | ✅ |
| 0.B.3.6 | Members relation (Attach/Detach/Edit role) | ✅ |
| 0.B.3.7 | Clickable member name → User edit page | ✅ |
| **0.B.4** | **Department management** | ✅ |
| 0.B.4.1 | DepartmentResource (CRUD) | ✅ |
| 0.B.4.2 | Team filter, search, sort | ✅ |
| **0.B.5** | **User management** | ✅ |
| 0.B.5.1 | UserResource (вместо EmployeeResource) | ✅ |
| 0.B.5.2 | Table: name, email, is_admin, companies, department, job_title | ✅ |
| 0.B.5.3 | Teams relation (Attach/Edit role/Detach) | ✅ |
| 0.B.5.4 | Clickable team name → Team edit page | ✅ |
| 0.B.5.5 | Employee relation (създаване/редакция на HR запис) | ✅ |
| **0.B.6** | **Seeders** | ✅ |
| 0.B.6.1 | TeamSeeder (5 компании) | ✅ |
| 0.B.6.2 | DepartmentSeeder (~19 отдела) | ✅ |
| 0.B.6.3 | UserSeeder (1 admin + 10 users) | ✅ |
| 0.B.6.4 | EmployeeSeeder (11 employees) | ✅ |

#### 0.C — Company Domain ✅ **ГОТОВО**

| Стъпка | Статус |
|--------|--------|
| **0.C.1** | **HR модели (Employee, Department)** | ✅ |
| **0.C.2** | **CompanyPanel** | ✅ |
| 0.C.2.1 | CompanyPanelProvider с `->path('{team:slug}/company')` (без `->tenant()`) | ✅ |
| 0.C.2.2 | Company theme (resources/css/filament/company/theme.css) | ✅ |
| 0.C.2.3 | User имплементира HasTenants (canAccessTenant, getTenants) | ✅ |
| 0.C.2.4 | EnsureTeamMembership middleware (setTenant + admin bypass) | ✅ |
| 0.C.2.5 | CompanyAuthenticate middleware (redirect to /login) | ✅ |
| 0.C.2.6 | Login redirect 500 error — fixed (Missing parameter: team) | ✅ |
| 0.C.2.7 | AdminAuthenticate middleware (redirect to /login) | ✅ |
| 0.C.2.8 | Премахнат ->login() от двата панела — единствен Fortify /login | ✅ |
| 0.C.2.9 | Login redirect: admin → /admin, regular → `/{team}/dashboard` (Inertia) | ✅ |
| **0.C.3** | **Company Resources** | ✅ |
| 0.C.3.1 | EmployeeResource (tenant-scoped via getEloquentQuery + whereBelongsTo) | ✅ |
| 0.C.3.2 | DepartmentResource (tenant-scoped via getEloquentQuery + whereBelongsTo) | ✅ |
| 0.C.3.3 | Create pages auto-fill team_id via mutateFormDataBeforeCreate | ✅ |

#### 0.C.4 — Company Panel Authorization ✅ **ГОТОВО**

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 0.C.4.1 | `EnsureTeamMembership:admin` — member → 403 | ✅ |
| 0.C.4.2 | Admin bypass в `ensureTeamMemberHasRequiredRole` | ✅ |
| 0.C.4.3 | `canCreate/canEdit/canDelete` в EmployeeResource | ✅ |
| 0.C.4.4 | `canCreate/canEdit/canDelete` в DepartmentResource | ✅ |
| 0.C.4.5 | `->visible()` на DeleteAction за enforceable canDelete | ✅ |

#### 0.C.5 — TeamRole & TeamPermission ✅ **ГОТОВО**

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 0.C.5.1 | TeamPermission enum — employee/department CRUD (6 нови case-a) | ✅ |
| 0.C.5.2 | TeamRole::Admin — всичко без delete права | ✅ |
| 0.C.5.3 | TeamRole::Owner — всички TeamPermission-и | ✅ |

#### 0.C.6 — Inertia Integration ✅ **ГОТОВО**

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 0.C.6.1 | Login redirect → `/{team}/dashboard` (Inertia) вместо `/admin`/`/{team}/company` | ✅ |
| 0.C.6.2 | Company nav link в sidebar footer-а (admin/owner) | ✅ |
| 0.C.6.3 | Team-switcher в sidebar-а (Laravel starter-kit default) | ✅ |

#### 0.C.7 — Test Users ✅ **ГОТОВО**

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 0.C.7.1 | `single@hrapp.app` — member на Acme Corp | ✅ |
| 0.C.7.2 | `singleadmin@hrapp.app` — admin на Acme Corp | ✅ |
| 0.C.7.3 | `singleowner@hrapp.app` — owner на Acme Corp | ✅ |
| 0.C.7.4 | UserSeeder — `firstOrCreate` за идемпотентност | ✅ |

---

#### Фаза 1 — Internationalization (i18n) ❌ **ПЛАНИРАНА**

##### 1.A — Core Translations (laravel-lang/common)

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.A.1 | `composer require laravel-lang/common` | ✅ |
| 1.A.2 | `php artisan lang:add bg` — core преводи (validation, auth, fortify, http-statuses) | ✅ |
| 1.A.3 | `composer.json` → `post-update-cmd` добавяме `@php artisan lang:update` | ✅ |

##### 1.B — App Translation Files

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.B.1 | Създаване `lang/en.json` с 5-10 ключа (nav, buttons, titles) | ❌ |
| 1.B.2 | Създаване `lang/bg.json` — копие с български превод | ❌ |
| 1.B.3 | `config/app.php` → `locale` = `env('APP_LOCALE', 'bg')`, `fallback_locale` = `en` | ❌ |
| 1.B.4 | Добавяне на `APP_LOCALE` и `APP_FALLBACK_LOCALE` в `.env` | ❌ |

##### 1.C — Locale Route & Controller

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.C.1 | `php artisan make:controller LocaleController` | ❌ |
| 1.C.2 | `change()` метод — валидация (само `en`/`bg`), запис в session | ❌ |
| 1.C.3 | `POST /locale` route с име `locale.change` | ❌ |
| 1.C.4 | `SetLocale` middleware — чете locale от session, вика `App::setLocale()` | ❌ |
| 1.C.5 | Регистриране на `SetLocale` в `web` middleware групата | ❌ |

##### 1.D — Inertia Bridge

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.D.1 | `HandleInertiaRequests` → share `locale` (от `App::getLocale()`) | ❌ |
| 1.D.2 | `HandleInertiaRequests` → share `translations` (load-ва JSON файла за текущия locale) | ❌ |
| 1.D.3 | Кеширане на translations per locale (`cache()->rememberForever`) | ❌ |

##### 1.E — React Hooks & Components

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.E.1 | `resources/js/hooks/useTranslation.ts` — `__()` и `trans()` от Inertia shared props | ❌ |
| 1.E.2 | `resources/js/components/language-switcher.tsx` — бутон за смяна на език | ❌ |
| 1.E.3 | Добавяне на LanguageSwitcher в sidebar header-а | ❌ |
| 1.E.4 | Превод на dashboard страницата като proof of concept | ❌ |
| 1.E.5 | Превод на sidebar navigation текстовете | ❌ |
| 1.E.6 | Превод на profile/settings страниците | ❌ |

##### 1.F — Filament Panels

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.F.1 | Проверка: Filament auto-използва `app()->getLocale()` | ❌ |
| 1.F.2 | Превод на navigation labels в CompanyPanel ресурсите | ❌ |
| 1.F.3 | Превод на form labels, table headers, съобщения | ❌ |

##### 1.G — Persistence (User Preference)

| Стъпка | Описание | Статус |
|--------|----------|--------|
| 1.G.1 | Migration: `users.locale` nullable string (2 chars) | ❌ |
| 1.G.2 | `HandleInertiaRequests` → чете locale от `auth()->user()->locale` | ❌ |
| 1.G.3 | `LocaleController` → записва locale и в user-а, и в session | ❌ |
| 1.G.4 | Appearance страницата — radio/select за език (EN/BG) | ❌ |

---

## 5. Admin Panel — текущ вид

### Навигация (3 секции)

| Секция | URL | Описание |
|--------|-----|----------|
| **Companies** | `/admin/teams` | CRUD компании. Edit → Members tab (с линк към User edit) |
| **Departments** | `/admin/departments` | CRUD отдели, филтър по компания |
| **Users** | `/admin/users` | Преглед на потребители. Edit → Teams tab (с линк към Team edit) + Employee tab |

### Key behaviors

- **Name колони** са кликабилни: member name → User edit, team name → Team edit
- **Team Role** се сменя от pencil иконата
- **Global Admin (`is_admin`)** в Members tab е премахнат — остава само Team Role
- **Employee** tab в User edit създава/редактира HR запис с company, department, job_title, hire_date
- Department select в Employee формата е dynamic — зависи от избраната компания

---

## 6. Следващи стъпки

1. **AbsenceType + LeaveRequest** — модели, миграции, фабрики, ресурси
2. **Фаза 1 — i18n** — двуезична поддръжка (EN + BG)

---

## 7. Технически решения

| Област | Решение |
|--------|---------|
| PHP | 8.5 |
| Framework | Laravel 13 |
| Frontend | React 19 + Inertia.js v3 |
| Admin panel | **FilamentPHP v5.6.6** |
| Database | MySQL 8+ |
| Auth | Laravel Fortify |
| Admin auth | `is_admin` boolean |
| Admin panel users | `admin@hrapp.app` / `password` |
| Regular multi-team user | `test@hrapp.app` / `password` (Acme Corp, Globex Inc, Initech) |
| Single-team users | `single@hrapp.app` (member), `singleadmin@hrapp.app` (admin), `singleowner@hrapp.app` (owner) — Acme Corp |
| UI | Radix UI + shadcn/ui (React) / Filament UI (admin) |
| Styling | Tailwind CSS v4 |
| Build | Vite 8 |
| Docker | Ръчен docker-compose |
| Typed routes | Laravel Wayfinder |

---

## 8. Бележки

- **Team = company.** Team моделът се използва като tenant/company контейнер.
- **Employee != User.** Employee е HR запис, User е auth акаунт.
- **Admins bypass TeamPolicy** — админите управляват всички компании.
- **Няма поддомейни** — path-based routing.
- **Една база за MVP.** Multi-tenancy с отделни бази — след MVP.
- **Два Filament панела:** AdminPanel (landlord) и CompanyPanel (tenant).
- **URL структура:** `/{team:slug}/company` (използваме `->path('{team:slug}/company')` без `->tenant()`, за да сложим slug-а отпред).
- **User имплементира `HasTenants`** — `canAccessTenant()` и `getTenants()` за tenant scoping (изисква се от Filament IdentifyTenant middleware).
- **Admin override:** `isAdmin()` потребителите достъпват всички teams от CompanyPanel.
- **Ръчно scoping:** Без `->tenant()`, Filament не auto-scope-ва. Използваме `getEloquentQuery()` + `whereBelongsTo()` и `mutateFormDataBeforeCreate()` за team_id.
- **Единен login:** И двата панела НЯМАТ `->login()`. Auth-а е само през Fortify `/login`. Panels redirect-ват към `/login`.
- **Login redirect:** `RedirectsToCurrentTeam` trait → admin отива на `/admin`, regular user отива на `/{team}/dashboard` (Inertia, не Filament).
- **Role-based panel access:** CompanyPanel изисква minimum role `admin` (чрез `EnsureTeamMembership:admin`). Системните админи (`isAdmin()`) прескачат role check-а.
- **Role-based CRUD:** `TeamRole::Admin` има всички permissions без delete. `TeamRole::Owner` има всичко. Контролът е чрез `canCreate/canEdit/canDelete` override-и в ресурсите + `->visible()` на DeleteAction.
- **TeamPermission enum:** `employee:create/update/delete` и `department:create/update/delete` — 6 нови case-a. Използва се от `hasTeamPermission()`.
- **CompanyAuthenticate/AdminAuthenticate:** Custom auth middlewares за всеки панел, redirect-ват към `/login` (вместо `Filament::getLoginUrl()`).

---

## 9. Команди

| Команда | Описание |
|---------|----------|
| `docker exec hrapp-laravel.test-1 php artisan ...` | Artisan в Docker |
| `docker exec hrapp-laravel.test-1 composer require ...` | Composer в Docker |
| `docker exec hrapp-laravel.test-1 npm run build` | Build |
| `docker exec hrapp-laravel.test-1 php artisan db:seed` | Seed данни |
| `docker exec hrapp-laravel.test-1 php artisan migrate:fresh --seed` | Fresh + seed |

| URL | Какво |
|-----|-------|
| http://localhost/admin | Admin Panel |
| http://localhost/admin/teams | Companies |
| http://localhost/admin/users | Users |
| http://localhost/admin/departments | Departments |
| http://localhost/acme-corp/dashboard | Inertia Dashboard |
| http://localhost/acme-corp/company | Company Panel (Filament dashboard) |
| http://localhost/acme-corp/company/employees | Company Employees |
| http://localhost/acme-corp/company/departments | Company Departments |

---

_Последна актуализация: 2026-06-01 (CompanyPanel authorization, Inertia login redirect, role-based CRUD, test users, i18n план)_

# Phase 1 — Internationalization (i18n) — Report

**Дата:** 2026-06-02
**Статус:** ✅ Завършена
**Езици:** English (en), Български (bg)

---

## 1.A — Core Translations (laravel-lang/common)

Инсталиран `laravel-lang/common` пакет за core преводи.

| Стъпка | Какво направихме |
|--------|-----------------|
| `composer require laravel-lang/common` | Инсталиран пакета |
| `php artisan lang:add bg` | Добавени core преводи на BG: validation, auth, passwords, pagination, http-statuses, actions |
| `composer.json post-update-cmd` | Добавен `@php artisan lang:update` за автоматично обновяване при `composer update` |

**Файлове:**
- `composer.json` — `post-update-cmd` скрипт

---

## 1.B — App Translation Files

Създадени специфични за приложението translation файлове.

| Стъпка | Какво направихме |
|--------|-----------------|
| `lang/en/app.php` | 150+ ключа на английски (всички UI текстове) |
| `lang/bg/app.php` | Същите ключове на български |
| `config/app.php` | `locale`, `fallback_locale`, `supported_locales` масив |
| `.env` | `APP_LOCALE=bg`, `APP_FALLBACK_LOCALE=en` |

**Ключови решения:**
- `supported_locales` конфигурация: `['en', 'bg']`
- Използва се от LocaleController за валидация и от HandleInertiaRequests за `supportedLocales` prop

**Файлове:**
- `lang/en/app.php`
- `lang/bg/app.php`

---

## 1.C — Locale Route & Controller

Създаден endpoint за смяна на езика.

| Стъпка | Какво направихме |
|--------|-----------------|
| `LocaleController` | `change()` метод с валидация `Rule::in(config('app.supported_locales'))` |
| `POST /locale` | Route с име `locale.change` |
| `SetLocale` middleware | Чете locale с приоритет: `auth()->user()->locale` > `session('locale')` > `config('app.locale')` |
| Middleware регистрация | `SetLocale` добавен в `web` middleware групата |

**Ключово решение:** LocaleController записва locale и в session, и в `auth()->user()->locale` (ако потребителят е логнат). Така изборът на език се помни между сесиите.

**Файлове:**
- `app/Http/Controllers/LocaleController.php`
- `app/Http/Middleware/SetLocale.php`
- `routes/web.php`

---

## 1.D — Inertia Bridge

Споделяне на locale и translations с React frontend-a.

| Стъпка | Какво направихме |
|--------|-----------------|
| `locale` | Shared prop — текущият locale от `app()->getLocale()` |
| `supportedLocales` | Shared prop — масив от поддържани езици |
| `translations` | Shared prop (deferred) — merge-нати `app.php` + `.json` преводи, кеширани `cache()->rememberForever` per locale |

**Ключови решения:**
- Translations се кешират forever per locale — `cache()->rememberForever('translations.en', ...)`
- Няма per-user cache — ако translation файловете се променят, cache трябва да се изчисти с `php artisan cache:clear`
- `supportedLocales` се чете от config, за да може лесно да се добавят нови езици

**Файлове:**
- `app/Http/Middleware/HandleInertiaRequests.php`

---

## 1.E — React Hooks & Components

React интеграция на преводите.

### 1.E.1 — useTranslation Hook

`resources/js/hooks/use-translation.ts`

- Връща `__()` функция за превод на ключове
- Връща `locale` — текущия език
- Връща `supportedLocales` — масив от поддържани езици
- `__()` поддържа placeholder replace (`:key` → стойност)

### 1.E.2 — LanguageSwitcher

`resources/js/components/language-switcher.tsx`

- Dropdown в SidebarFooter (между NavFooter и NavUser)
- Показва EN/BG с текущия избран език
- Изпраща POST `locale.change` с новия locale

### 1.E.3–6 — Преведени страници (20 файла)

| Страница | Файлове |
|----------|---------|
| Dashboard | `dashboard.tsx` |
| Sidebar | sidebar компоненти |
| Profile settings | `settings/profile.tsx` |
| Security settings | `settings/security.tsx` |
| Appearance settings | `settings/appearance.tsx` |
| Teams index | `teams/index.tsx` |
| Team edit | `teams/edit.tsx` |
| Sub-components | `delete-user.tsx`, `appearance-tabs.tsx`, `manage-passkeys.tsx`, `manage-two-factor.tsx`, `modals/*`, `passkey/*`, `2fa/*` |

**Файлове:** ~25 React компонента

---

## 1.F — Filament Panels

Локализация на двата Filament панела (AdminPanel + CompanyPanel).

### Какво направихме:

**Resource model labels (5 класа):**
- `UserResource` → `getModelLabel()` връща `__('User')`, `getPluralModelLabel()` връща `__('Users')`
- `TeamResource` → `__('Team')` / `__('Teams')`
- `DepartmentResource` (Admin) → `__('Department')` / `__('Departments')`
- `EmployeeResource` (Company) → `__('Employee')` / `__('Employees')`
- `DepartmentResource` (Company) → `__('Department')` / `__('Departments')`

Това auto-пропагира до:
- Navigation labels в sidebar-а
- Page titles (List/Create/Edit)
- Breadcrumbs

**Form fields & Table columns:**
- Всеки `->label(...)` е обвит в `__()`
- Всички auto-generated labels (от `make('name')`) са експлицитно зададени с `->label(__('...'))`

**Relation managers (3 класа):**
- `EmployeeRelationManager` — Company, Department, First name, Last name, Email, Phone, Job title, Hire date
- `TeamsRelationManager` — Name, Role, Team Role
- `MembersRelationManager` — Name, Department, Team Role, Role

**TeamRole enum:**
- `label()` методът връща `__(ucfirst($this->value))` — Admin/Member/Owner сега са translatable

**Filter options:**
- `Yes`/`No` в UsersTable филтъра обвити в `__()`

**Файлове:** 17 PHP файла в `app/Filament/`

---

## 1.G — Persistence (User Preference)

Запазване на избрания език в базата, за да не се забравя между сесиите.

| Стъпка | Какво направихме |
|--------|-----------------|
| Migration | `users.locale` — nullable string(2) |
| `SetLocale` | Приоритет: `auth()->user()->locale` > `session('locale')` > `config('app.locale')` |
| `LocaleController` | Записва locale и в `auth()->user()->locale`, и в `session('locale')` |
| Appearance страница | Добавен radio selector за EN/BG с `useTranslation().supportedLocales` |

**Ключово решение:** При незалогнат потребител locale-ът се пази само в session. След логване, ако има session locale, но user-ът няма locale, session-ът се синхронизира — но най-чистият flow е: user избира език → записва се в DB → при всяка следваща заявка `SetLocale` го чете от DB.

**Файлове:**
- `database/migrations/xxxx_add_locale_to_users_table.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Controllers/LocaleController.php`
- `resources/js/pages/settings/appearance.tsx`
- `resources/js/hooks/use-translation.ts`

---

## Обобщение на всички модифицирани файлове

### PHP (backend)
- `app/Http/Controllers/LocaleController.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Enums/TeamRole.php`
- `app/Models/User.php` (+ `'locale'` във fillable)
- `routes/web.php`
- `config/app.php` (+ `supported_locales`)
- `.env` (+ `APP_LOCALE=bg`)
- `lang/en/app.php` (150+ keys)
- `lang/bg/app.php` (150+ keys)
- `database/migrations/xxxx_add_locale_to_users_table.php`
- 17 Filament файла (Resources, Schemas, Tables, RelationManagers)

### TypeScript/React (frontend)
- `resources/js/hooks/use-translation.ts`
- `resources/js/components/language-switcher.tsx`
- ~20 React page/component файла

---

## Пропуснати / бъдещи

Следните файлове НЕ са преведени в тази фаза — може да се добавят по-късно:

- **7 auth страници** — `login.tsx`, `register.tsx`, `forgot-password.tsx`, `reset-password.tsx`, `verify-email.tsx`, `confirm-password.tsx`, `two-factor-challenge.tsx`
- **`welcome.tsx`** — welcome страницата
- **`app-header.tsx`** — header navigation (Repository, Documentation, Dashboard)
- **`team-switcher.tsx`** — Select team, Teams, New team
- **`passkey-verify.tsx`** — fallback/default labels
- **`colleague@example.com`** — placeholder ключ липсва в lang файловете

---

## Технически детайли

### Как работи `__()` в React:
```ts
// use-translation.ts
const __: TranslateFn = (key, replace) => {
    let translation = translations[key] ?? key;
    if (replace) {
        for (const [search, value] of Object.entries(replace)) {
            translation = translation.replace(`:${search}`, String(value));
        }
    }
    return translation;
};
```

### Как работи `__()` в PHP (Filament/Blade):
```php
->label(__('Name'))
// Laravel-овият __() хваща от lang/{locale}/app.php или от lang/{locale}.json
```

### Поток на locale:
```
User избира език от LanguageSwitcher (dropdown) или Appearance (radio)
  → POST /locale → LocaleController::change()
    → валидация (supported_locales)
    → session('locale', 'bg')
    → auth()->user()->update(['locale' => 'bg'])  // (ако е логнат)
  → redirect back()

SetLocale middleware (на всяка заявка):
  → auth()->user()?->locale ?? session('locale') ?? config('app.locale')
  → App::setLocale(...)

HandleInertiaRequests:
  → share 'locale' => app()->getLocale()
  → share 'supportedLocales' => конфигурация
  → share 'translations' => app.php + .json (кеширано)
```

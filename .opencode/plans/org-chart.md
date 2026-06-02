# Org Chart / Organizational Hierarchy — Plan

**Дата:** 2026-06-02
**Статус:** ⏳ Планиране
**Връзка:** [PLAN.md](PLAN.md) → Фаза 4

---

## 1. Feature Overview

Дървовидна йерархична структура на компанията — department tree с parent-child отношения, мениджъри на отдели и reporting lines (на кого се отчита служителят). Служителите и отделите се организират в йерархия от CEO до отделните екипи.

**Ключови въпроси, на които отговаря:**
- Кой на кого се отчита (reporting line)?
- Кой ръководи даден отдел?
- Каква е структурата на компанията (department tree)?
- Колко души има под даден мениджър (headcount)?

---

## 2. Competitive Benchmark

| Feature | BambooHR | Personio | **MVP** | **Advanced** |
|---------|----------|----------|---------|--------------|
| Department parent_id (tree hierarchy) | ✅ | ✅ | **✅** | ✅ |
| Department manager/head | ✅ | ✅ | **✅** | ✅ |
| Employee reports_to (manager) | ✅ | ✅ | **✅** | ✅ |
| Collapsible tree table (Filament) | ❌ | ✅ | **✅** | ✅ |
| Visual org chart (d3.js / SVG) | ✅ | ✅ | ❌ | ✅ |
| Levels / layers (Personio up to 10) | ✅ | ✅ | ❌ | ✅ |
| Dotted reporting lines (matrix org) | ❌ | ✅ | ❌ | ✅ |
| Spotlight (full reporting line of one person) | ✅ | ✅ | ❌ | ✅ |
| Floating cards (employees without manager) | ❌ | ✅ | ❌ | ✅ |
| Pin to employee (focus) | ✅ | ❌ | ❌ | ✅ |
| Export PDF / PNG / CSV | ✅ | ❌ | ❌ | ✅ |
| Open positions on org chart | ❌ | ✅ | ❌ | ✅ |
| Permission-based visibility | ❌ | ✅ | ❌ | ✅ |
| Headcount per node | ❌ | ✅ | ❌ | ✅ |
| Quick link от employee profile | ✅ | ✅ | ❌ | ✅ |
| Mobile-friendly view | ✅ | ✅ | ❌ | ✅ |
| Succession planning | ❌ | ❌ | ❌ | ✅ |
| Org chart от employee directory | ✅ | ✅ | ❌ | ✅ |

**Sources:**
- BambooHR Org Chart docs — https://help.bamboohr.com/s/article/587751
- BambooHR Full Reporting Line — https://www.bamboohr.com/product-updates/full-reporting-line-in-the-org-chart
- BambooHR Org Chart Quick Link — https://www.bamboohr.com/product-updates/org-chart-quick-link
- Personio Org Chart overview — https://support.personio.de/hc/en-us/articles/360017540757-Overview-of-the-Org-chart
- Personio Departments and Teams — https://support.personio.de/hc/en-us/articles/18862110441885-Set-up-departments-and-teams
- Personio Department hierarchy summary — https://support.personio.de/hc/en-us/articles/33552992365597-Summary-of-departments-and-teams
- Personio Implement basic structure — https://support.personio.de/hc/en-us/articles/21495336264733-Implement-your-company-s-basic-structure
- Best org chart software 2026 — https://www.softr.io/blog/best-org-chart-software

---

## 3. Database промени

Добавят се 3 колони към съществуващи таблици:

```sql
-- departments: parent (кой отдел е над този) + manager (кой го ръководи)
ALTER TABLE departments
  ADD COLUMN parent_id    bigint FK → departments nullable,
  ADD COLUMN manager_id   bigint FK → employees nullable;

-- employees: на кого се отчита служителят
ALTER TABLE employees
  ADD COLUMN reports_to_id bigint FK → employees nullable;
```

**Обосновка:**
- `departments.parent_id` — само-рефериращ FK за дървовидна структура (CEO → IT → Development → Frontend team)
- `departments.manager_id` — лидерът на отдела (служител от компанията)
- `employees.reports_to_id` — прекият мениджър на служителя (може да води друг отдел)

**Ограничения:**
- Един служител може да бъде manager на само един отдел (в MVP)
- Един служител се отчита на точно един мениджър (в MVP — без matrix/dotted lines)
- `parent_id` не може да сочи към собствения си ID (цикличност)
- Максимална дълбочина на йерархията: 10 нива (като Personio)

---

## 4. Architecture

```
app/
├── Models/
│   ├── Department.php     (промяна: + parent_id, manager_id)
│   ├── Employee.php       (промяна: + reports_to_id)
│   └── ... (съществуващи)
│
├── Filament/Company/
│   ├── Resources/
│   │   ├── DepartmentResource.php  (промяна: + parent/manager field)
│   │   └── EmployeeResource.php    (промяна: + reports_to поле)
│   └── Widgets/
│       └── OrgTreeWidget.php       ⏳ бъдещо (advanced visual)
│
├── Filament/Resources/
│   └── Departments/
│       ├── DepartmentResource.php  (промяна: + parent/manager)
│       └── ...
│
├── Enums/
│   └── TeamPermission.php  (нови case-ове)
│
└── Livewire/
    └── OrgChart.php        (⏳ бъдещо — custom Livewire компонент за визуално дърво)
```

---

## 5. MVP scope (Phase 4.A)

### 5.1 — Department tree

**Какво се променя в `DepartmentResource`:**

- `DepartmentForm` — добавя се `Select` за `parent_id`:
  ```php
  Select::make('parent_id')
      ->label(__('Parent Department'))
      ->relationship('parent', 'name')
      ->searchable()
      ->preload(),
  ```
- `DepartmentForm` — добавя се `Select` за `manager_id`:
  ```php
  Select::make('manager_id')
      ->label(__('Manager'))
      ->relationship('manager', 'full_name')
      ->searchable()
      ->preload(),
  ```
- `DepartmentsTable` — колона `Parent` (име на родителския отдел)
- `DepartmentsTable` — колона `Manager` (име на мениджъра)
- Validation: `parent_id != id` (пазим от цикличност)
- Scope: parent select показва само отдели от същия team

**Model `Department` — нови релации:**
```php
public function parent(): BelongsTo
{
    return $this->belongsTo(self::class, 'parent_id');
}

public function children(): HasMany
{
    return $this->hasMany(self::class, 'parent_id');
}

public function manager(): BelongsTo
{
    return $this->belongsTo(Employee::class, 'manager_id');
}
```

**Model `Employee` — нова релация:**
```php
public function manager(): BelongsTo
{
    return $this->belongsTo(self::class, 'reports_to_id');
}

public function directReports(): HasMany
{
    return $this->hasMany(self::class, 'reports_to_id');
}
```

### 5.2 — Employee reports_to

**Какво се променя в `EmployeeResource`:**

- `EmployeeForm` — добавя се `Select` за `reports_to_id`:
  ```php
  Select::make('reports_to_id')
      ->label(__('Reports To'))
      ->relationship('manager', 'full_name')
      ->searchable()
      ->preload(),
  ```
- Validations: `reports_to_id != id` (не може да се отчита на себе си)

### 5.3 — Admin Panel

Същите промени за `AdminPanel` → `app/Filament/Resources/Departments/`

### 5.4 — Permissions

Нови `TeamPermission` case-ове:

```php
case UpdateDepartmentHierarchy = 'department:hierarchy'; // parent_id
case AssignDepartmentManager = 'department:manager';     // manager_id
case AssignEmployeeManager = 'employee:manager';         // reports_to_id
```

**TeamRole mapping:**
- `Owner` → всички
- `Admin` → всички
- `Editor` → само `employee:manager` (да задава reports_to на подчинените си)
- `Viewer` → read-only

### 5.5 — Validation & business rules

**При избор на `parent_id`:**
- Не може да сочи към себе си
- Не може да създава цикъл (A → B → C → A) — проверка при save
- Един отдел може да има само един родител

**При избор на `manager_id`:**
- Мениджърът трябва да е служител в същата компания (team)
- Един служител може да е manager на няколко отдела
- `reports_to_id` трябва да сочи към служител от същия team

---

## 6. Advanced scope (Phase 4.B+)

| Feature | Описание |
|---------|----------|
| **Visual org chart** | Интерактивно дърво с d3.js или react-tree-graph — zoom, pan, click |
| **Levels / layers** | Auto-изчисляване на layer (1-10) базирано на parent_id дълбочина |
| **Spotlight** | Клик на служител → виждаш цялата му reporting line нагоре и надолу |
| **Floating cards** | Служители без `reports_to_id` се показват най-отгоре |
| **Export PDF/PNG** | Експорт на org chart като изображение |
| **Headcount per node** | Брой хора под всеки отдел/мениджър |
| **Open positions** | Показване на неприети позиции в структурата |
| **Quick link от employee profile** | "View in org chart" бутон |
| **Dotted reporting lines** | Matrix структура — служител с multiple supervisors |
| **Mobile view** | Адаптивен изглед за телефон |
| **Succession planning** | Отбелязване на potential successors за ключови позиции |

---

## 7. Database migration

```php
// php artisan make:migration add_hierarchy_to_departments_and_employees
Schema::table('departments', function (Blueprint $table) {
    $table->foreignId('parent_id')
        ->nullable()
        ->constrained('departments')
        ->nullOnDelete()
        ->after('description');

    $table->foreignId('manager_id')
        ->nullable()
        ->constrained('employees')
        ->nullOnDelete()
        ->after('parent_id');
});

Schema::table('employees', function (Blueprint $table) {
    $table->foreignId('reports_to_id')
        ->nullable()
        ->constrained('employees')
        ->nullOnDelete()
        ->after('department_id');
});
```

---

## 8. Visual hierarchy example

```
CEO (собственик/управител)             ← reports_to = null
├── IT отдел (manager: CTO)           ← parent_id = null, manager_id = CTO
│   ├── Development (manager: Dev Mgr) ← parent_id = IT
│   │   ├── Frontend (manager: Lead)   ← parent_id = Development
│   │   └── Backend (manager: Lead)    ← parent_id = Development
│   └── DevOps (manager: DevOps Lead)  ← parent_id = IT
├── HR отдел (manager: HR Director)    ← parent_id = null
└── Finance (manager: CFO)             ← parent_id = null
```

**Employee reporting:**
```
CTO → reports_to = CEO
Dev Mgr → reports_to = CTO
Frontend Lead → reports_to = Dev Mgr
Frontend Dev 1 → reports_to = Frontend Lead
HR Director → reports_to = CEO
HR Specialist → reports_to = HR Director
```

---

## 9. Lang keys

```php
// lang/{locale}/app.php
'Parent Department' => 'Родителски отдел',
'Manager' => 'Ръководител',
'Reports To' => 'Отчита се на',
'Direct Reports' => 'Преки подчинени',
'Organization Chart' => 'Организационна структура',
'Hierarchy' => 'Йерархия',
'Level' => 'Ниво',
'Floating (no manager)' => 'Без ръководител',
'Full Reporting Line' => 'Пълна линия на докладване',
```

---

## 10. Какво НЕ правим (out of scope за MVP)

- Visual org chart с d3.js / SVG — ⏳ за Phase 4.B
- Matrix / dotted lines — само single manager
- Succession planning — ⏳ бъдещо
- Export (PDF/PNG) — ⏳ бъдещо
- Mobile view — уеб-first

---

_Последна актуализация: 2026-06-02_

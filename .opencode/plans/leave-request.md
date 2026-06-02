# Leave Request / Absence Management — Plan

**Дата:** 2026-06-02
**Статус:** ⏳ Планиране
**Връзка:** [PLAN.md](PLAN.md) → Фаза 2

---

## 1. Feature Overview

Модул за управление на отсъствия (leave/absence management) — служителите подават заявки за отпуск, мениджърите одобряват/отхвърлят, системата следи баланси.

---

## 2. Competitive Benchmark

Проучване на водещи HR SaaS платформи спрямо нашето планирано MVP и бъдещи функционалности.

| Feature | BambooHR | Personio | BreezeLeave | **MVP** | **Advanced** |
|---------|----------|----------|-------------|---------|--------------|
| Absence types (vacation, sick, etc.) | ✅ | ✅ | ✅ | **✅ Phase 2.A** | ✅ |
| Request → Approve workflow | ✅ | ✅ | ✅ | **✅ Phase 2.A** | ✅ |
| Balance tracking (taken/remaining) | ✅ | ✅ | ✅ | **✅ Phase 2.B** | ✅ |
| Team calendar (Who's out) | ✅ | ✅ | ✅ | **✅ Phase 2.B** | ✅ |
| Half-day support | ✅ | ✅ | ✅ | **✅ Phase 2.A** | ✅ |
| Custom absence type (color, paid/unpaid) | ✅ | ✅ | ✅ | **✅ Phase 2.A** | ✅ |
| Multi-level approval | ✅ | ✅ | ✅ | ❌ MVP | ✅ |
| Auto-approval engine (rules-based) | ❌ | ❌ | ✅ | ❌ | ✅ |
| Accrual (monthly/tenure-based) | ✅ | ✅ | ✅ | ❌ | ✅ |
| Carry-over / rollover rules | ✅ | ✅ | ✅ | ❌ | ✅ |
| Pro-rata for mid-year joiners | ✅ | ✅ | ✅ | ❌ | ✅ |
| Public holiday calendars (240+ countries) | ❌ | ✅ | ✅ | ❌ | ✅ |
| Sandwich rule | ❌ | ✅ | ❌ | ❌ | ✅ |
| Google/Outlook Calendar sync | ❌ | ✅ | ✅ | ❌ | ✅ |
| Email notifications | ✅ | ✅ | ✅ | ❌ | ✅ |
| Reporting (absenteeism, utilization) | ✅ | ✅ | ✅ | ❌ | ✅ |
| Slack/Teams integration | ❌ | ❌ | ✅ | ❌ | ✅ |
| AI assistant (balance check, request) | ❌ | ❌ | ✅ | ❌ | ✅ |

**Sources:**
- BambooHR Time Off — https://www.bamboohr.com/platform/time-and-attendance/time-off
- Personio Absence Management — https://www.personio.com/product/absence-management/
- BreezeLeave Features — https://breezeleave.com/features
- "Leave Management System Low-Level Design" — techinterview.org
- "Attendance & Leave Management Software Buying Checklist 2026" — hrone.cloud
- "How to Choose the Right Leave Management System Vendor" — data-basics.com

---

## 3. MVP Phases

### Phase 2.A — Core (Absence Types + Leave Requests)

Минимално необходимо за работещ модул.

**Models:**

```php
AbsenceType  →  belongsTo Team
LeaveRequest →  belongsTo Employee, belongsTo AbsenceType,
                belongsTo User (approved_by)
```

**Database Schema:**

```sql
absence_types:
  id              bigint PK
  team_id         bigint FK → teams
  name            varchar(255)         // "Vacation", "Sick Leave", "Personal"
  color           varchar(7)            // #hex code за visual tag
  is_paid         boolean               // платен/неплатен отпуск
  requires_doc    boolean               // изисква документ (болничен)
  sort            integer               // за подредба в UI
  created_at, updated_at
  UNIQUE(team_id, name)

leave_requests:
  id              bigint PK
  team_id         bigint FK → teams
  employee_id     bigint FK → employees
  absence_type_id bigint FK → absence_types
  status          enum(pending,approved,rejected,cancelled)
  start_date      date
  end_date        date
  is_half_day     boolean default false
  reason          text nullable
  approved_by     bigint FK → users nullable
  approved_at     timestamp nullable
  cancelled_by    bigint FK → users nullable
  cancelled_at    timestamp nullable
  created_at, updated_at
```

**Filament Resources (Company Panel):**

| Resource | Навигация | Действия |
|----------|-----------|----------|
| **AbsenceTypeResource** | Отсъствия → Типове | CRUD (само admin/owner) |
| **LeaveRequestResource** | Отсъствия → Заявки | Submit, Approve, Reject, Cancel |

**Status Workflow:**

```
pending ───→ approved    (manager/owner/admin)
pending ───→ rejected    (manager/owner/admin)
pending ───→ cancelled   (self, преди approval)
approved ─→ cancelled    (manager/owner/admin)
```

**Validation Rules:**
- `end_date >= start_date`
- Бизнес дни (без weekends) — бройка дни за баланса
- Overlap detection — не може да има 2 одобрени отсъствия за едни и същи дати
- `AbsenceType`-ът трябва да принадлежи на същия team

**Permissions (TeamPermission enum — нови case-ове):**

```php
case CreateAbsenceType = 'absence_type:create';
case UpdateAbsenceType = 'absence_type:update';
case DeleteAbsenceType = 'absence_type:delete';
case CreateLeaveRequest = 'leave_request:create';     // всеки employee
case UpdateLeaveRequest = 'leave_request:update';
case ApproveLeaveRequest = 'leave_request:approve';   // admin/owner
```

**TeamRole mapping:**
- `Admin` → всички `absence_type:*`, `leave_request:approve`, `leave_request:update`
- `Owner` → всичко
- `Editor` → `leave_request:create` (собствени), `leave_request:update` (собствени)
- `Viewer` → `leave_request:create` (собствени)

**Authorisation:**
- `canCreate` / `canEdit` / `canDelete` override-и в Resources
- `->visible()` на бутоните според `hasTeamPermission()`
- Employee scoping: employee вижда само собствените си заявки
- Manager/admin/owner виждат заявките на целия team

---

### Phase 2.B — Balance & Visibility

**Leave Balance:**
- Auto-calculated от approved заявки (брой бизнес дни)
- Показване на `taken`, `remaining`, `total` per absence type
- Widget на dashboard-а: "Вашите отпуски" (your balances)

**Team Calendar (Who's Out):**
- Widget на dashboard-а: "Кой е в отпуск?"
- Team calendar view — всички одобрени отсъствия за избрания период
- Цветове според типа отсъствие (от `absence_type.color`)
- Избор на период (днес, тази седмица, този месец)

**Stats Widgets:**
- Pending approvals count (за managers)
- Upcoming absences (следващите 7 дни)
- Team availability % за днес

---

### Phase 2.C (Advanced — бъдещо)

| Feature | Описание |
|---------|----------|
| **Accrual** | Месечно натрупване (1.67 дни/месец за vacation). Batch job. |
| **Carry-over** | Неизползвани дни преминават в новата година (с капацитет/expiry) |
| **Pro-rata** | Пропорционално за mid-year joiners и напускащи |
| **Public holidays** | Календар с празниците на България и други държави |
| **Auto-approval** | Ако балансът е достатъчен и няма overlap → auto-approved |
| **Multi-level approval** | >5 дни → HR approval след мениджър |
| **Sandwich rule** | Уикенд между два отпуска се брои за отпуска |
| **Google Calendar sync** | Одобрените отсъствия → all-day event в Google Calendar |
| **Email notifications** | pending, approved, rejected, cancelled, reminders |
| **Reports** | Absenteeism report, utilization by department, monthly trends |
| **Slack/Teams bot** | `/whoisoff`, Approve/Reject от Slack |

---

## 4. Architecture

```
app/
├── Models/
│   ├── AbsenceType.php
│   ├── LeaveRequest.php
│   └── ... (съществуващи)
│
├── Enums/
│   ├── LeaveRequestStatus.php   (pending, approved, rejected, cancelled)
│   └── TeamPermission.php       (нови case-ове)
│
├── Filament/Company/
│   └── Resources/
│       ├── AbsenceTypeResource.php
│       │   └── Schemas/
│       │       └── AbsenceTypeForm.php
│       │   └── Tables/
│       │       └── AbsenceTypesTable.php
│       ├── LeaveRequestResource.php
│       │   └── Schemas/
│       │       └── LeaveRequestForm.php
│       │   └── Tables/
│       │       └── LeaveRequestsTable.php
│       └── Widgets/
│           ├── LeaveBalanceWidget.php
│           ├── WhosOutWidget.php
│           └── PendingApprovalsWidget.php
│
├── Policies/
│   └── LeaveRequestPolicy.php (ако е нужно)
│
└── Http/Middleware/
    └── ... (съществуващи)
```

### Key Implementation Details

**Business Days Calculation:**
```php
// Всички отсъствия се броят в бизнес дни (без събота/неделя)
// Option: използваме Carbon с ->diffInDaysFiltered()
$startDate->diffInDaysFiltered(fn (Carbon $date) => ! $date->isWeekend(), $endDate);
```

**Leave Balance — подход без отделна таблица:**
```php
// Изчислява се динамично от approved заявките
// Няма отделен LeaveBalance модел — по-малко sync проблеми
$taken = LeaveRequest::where('employee_id', $employee->id)
    ->where('absence_type_id', $type->id)
    ->where('status', 'approved')
    ->whereYear('start_date', $year)
    ->get()
    ->sum(fn ($r) => $r->businessDaysCount());
```

**Overlap Detection:**
```php
$overlap = LeaveRequest::where('employee_id', $employee->id)
    ->whereIn('status', ['pending', 'approved'])
    ->where(function ($q) use ($start, $end) {
        $q->whereBetween('start_date', [$start, $end])
          ->orWhereBetween('end_date', [$start, $end])
          ->orWhere(function ($q) use ($start, $end) {
              $q->where('start_date', '<=', $start)
                ->where('end_date', '>=', $end);
          });
    })
    ->exists();
```

---

## 5. Технически детайли

### Lang keys (за Phase 2 — ако i18n е активна тогава)

```php
// lang/{locale}/app.php
'Leave Types' => 'Видове отпуски',
'Leave Requests' => 'Заявки за отпуск',
'Vacation' => 'Годишен отпуск',
'Sick Leave' => 'Болничен',
'Personal' => 'Личен',
'Approved' => 'Одобрен',
'Pending' => 'Чакащ',
'Rejected' => 'Отхвърлен',
'Cancelled' => 'Анулиран',
'Balance' => 'Баланс',
'Who\'s Out' => 'Кой е в отпуск',
'days' => 'дни',
'pending approval' => 'чакащ одобрение',
'upcoming absence' => 'предстоящо отсъствие',
```

### Widget positions

- `LeaveBalanceWidget` → Dashboard (employee view) — 2 колони
- `WhosOutWidget` → Dashboard (team view) — 3 колони
- `PendingApprovalsWidget` → Dashboard (manager view) — 2 колони

---

## 6. Какво НЕ правим (out of scope за MVP)

- Timesheet / времево присъствие (check-in/check-out)
- Overtime изчисления
- Извънреден труд
- Командировки (business trips)
- Отпуск за сметка на работодателя (parental leave)
- Интеграция с външни payroll системи
- Mobile app (уеб-first)

---

## 7. Референции

- **BambooHR Time Off** — https://www.bamboohr.com/platform/time-and-attendance/time-off
- **Personio Absence Management** — https://www.personio.com/product/absence-management/
- **BreezeLeave Features** — https://breezeleave.com/features
- **Leave Management LLD** — https://www.techinterview.org/post/3233464881/lld-employee-leave-management/
- **HR Buying Checklist 2026** — https://hrone.cloud/blog/attendance-leave-management-software-detailed-buying-checklist/
- **DATABASICS Vendor Guide** — https://blog.data-basics.com/how-to-choose-leave-management-system-vendor

---

_Последна актуализация: 2026-06-02_

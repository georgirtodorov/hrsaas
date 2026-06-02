# Desk & Room Booking / Workplace Management — Plan

**Дата:** 2026-06-02
**Статус:** ⏳ Планиране (идея)
**Връзка:** [PLAN.md](PLAN.md) → Фаза 5

---

## 1. Feature Overview

Интерактивна карта на офиса с визуално разположение на бюрата — служителите виждат кое бюро на кого е, кои са свободни, могат да букват за конкретен ден или час. Включва и букване на зали за срещи.

**Ключови въпроси, на които отговаря:**
- Кой на кое бюро седи?
- Кои бюра са свободни днес?
- Къде е {служител}?
- Мога ли да запазя място за утре?
- Коя зала е свободна в 14:00?

---

## 2. Competitive Benchmark

| Feature | Envoy | Robin | Flexopus | DeskGrid (OSS) | Roomer (OSS) |
|---------|-------|-------|----------|----------------|--------------|
| Floor plan (image + placed desks) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Permanent desk assignment | ✅ | ✅ | ✅ | ✅ | ✅ |
| Hot desking (book by date) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Room booking (meeting rooms) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Interactive canvas (live availability) | ✅ | ✅ | ✅ | ❌ | ✅ (Konva) |
| Zones (quiet, collab, focus) | ✅ | ✅ | ✅ | ❌ | ✅ |
| QR code check-in | ✅ | ✅ | ✅ | ❌ | ❌ |
| No-show auto-release | ✅ | ✅ | ✅ | ❌ | ❌ |
| Waitlist / queue | ❌ | ❌ | ❌ | ❌ | ✅ |
| Calendar sync (Outlook/Google) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Analytics (utilization %) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Visitor/guest booking | ✅ | ✅ | ✅ | ❌ | ❌ |
| Desk lending (reverse hoteling) | ✅ (2026) | ❌ | ❌ | ❌ | ❌ |
| Multi-office | ✅ | ✅ | ✅ | ✅ | ✅ |
| Mobile app | ✅ | ✅ | ✅ | ❌ | ❌ |
| Slack/Teams integration | ✅ | ✅ | ✅ | ❌ | ❌ |
| Open source / self-hosted | ❌ | ❌ | ❌ | ✅ (MIT) | ✅ (MIT) |

**Sources:**
- Envoy Desk Booking — https://envoy.com/products/hot-desk-booking-software
- Robin Desk Booking — https://robinpowered.com/platform/desk-booking
- Flexopus Desk Booking — https://www.flexopus.com/en/blog-posts/desk-booking-tool
- DeskGrid (Laravel OSS) — https://github.com/m1thrandir225/deskgrid
- Roomer (React+Konva OSS) — https://github.com/c0dewhacker/Roomer
- WARP (PHP OSS) — https://github.com/codehausau/warp
- LibreBooking — https://github.com/LibreBooking/librebooking
- OpenDesk — https://github.com/kanwalnainsingh/opendesk

---

## 3. Database Schema

```sql
-- Всички таблици са tenant-scoped (team_id FK) — всяка компания има своите офиси

offices:
  id              bigint PK
  team_id         bigint FK → teams
  name            varchar(255)
  address         text nullable
  timezone        varchar(64) default 'UTC'
  created_at, updated_at

floors:
  id              bigint PK
  office_id       bigint FK → offices
  name            varchar(255)          // "Първи етаж", "Second Floor"
  floor_number    integer               // за подредба
  floor_plan_url  varchar(2048)         // SVG/PNG карта на етажа
  width           integer               // ширина на canvas в px
  height          integer               // височина на canvas в px
  created_at, updated_at

desks:
  id              bigint PK
  floor_id        bigint FK → floors
  label           varchar(50)           // "A12", "B04", "Рецепция"
  type            enum(permanent,hot,visitor)
  pos_x           decimal(10,4)         // % позиция върху картата (0–100)
  pos_y           decimal(10,4)
  width           decimal(10,4)         // ширина на бюрото в %
  height          decimal(10,4)         // височина на бюрото в %
  rotation        integer default 0     // градуси за desk ротация
  amenities       json nullable         // ["monitor", "dock", "ergonomic"]
  is_active       boolean default true
  sort            integer default 0
  created_at, updated_at

rooms:
  id              bigint PK
  floor_id        bigint FK → floors
  name            varchar(255)          // "Зала А", "Conference Room B"
  capacity        integer
  pos_x, pos_y, width, height, rotation
  amenities       json nullable         // ["projector", "whiteboard", "tv"]
  is_active       boolean default true
  created_at, updated_at

desk_assignments:
  id              bigint PK
  desk_id         bigint FK → desks
  employee_id     bigint FK → employees
  start_date      date
  end_date        date nullable         // null = ongoing
  created_at, updated_at

desk_bookings:
  id              bigint PK
  desk_id         bigint FK → desks
  employee_id     bigint FK → employees
  booking_date    date
  is_half_day     enum(full,am,pm) default 'full'
  status          enum(confirmed,cancelled,no_show)
  checked_in_at   timestamp nullable
  created_at, updated_at
  -- UNIQUE(desk_id, booking_date, is_half_day)

room_bookings:
  id              bigint PK
  room_id         bigint FK → rooms
  employee_id     bigint FK → employees
  title           varchar(255)          // "Weekly Sync"
  start_datetime  datetime
  end_datetime    datetime
  status          enum(confirmed,cancelled)
  created_at, updated_at
  -- предотвратява overlap: room_id + status=confirmed + datetime overlap
```

---

## 4. Architecture

```
app/
├── Models/
│   ├── Office.php
│   ├── Floor.php
│   ├── Desk.php
│   ├── Room.php
│   ├── DeskAssignment.php
│   ├── DeskBooking.php
│   ├── RoomBooking.php
│   └── ... (съществуващи)
│
├── Filament/Company/
│   ├── Resources/
│   │   ├── OfficeResource.php
│   │   ├── FloorResource.php
│   │   ├── DeskResource.php
│   │   ├── RoomResource.php
│   │   ├── DeskAssignmentResource.php
│   │   ├── DeskBookingResource.php
│   │   └── RoomBookingResource.php
│   └── Widgets/
│       ├── FloorPlanWidget.php         (интерактивна карта — Phase 5.A)
│       └── TodayOccupancyWidget.php    (кой е в офиса днес)
│
├── Livewire/
│   └── FloorPlanEditor.php            (Phase 5.B — drag-drop canvas редактор)
│
└── Enums/
    ├── DeskType.php                    (permanent, hot, visitor)
    ├── BookingStatus.php               (confirmed, cancelled, no_show)
    └── HalfDayEnum.php                 (full, am, pm)
```

---

## 5. MVP scope (Phase 5.A)

### 5.A.1 — Offices + Floors CRUD

- `OfficeResource` — name, address, timezone
- `FloorResource` — office_id, name, floor_number, floor_plan_url (Upload файл)
- Всички следващи ресурси са scoped до текущия team

### 5.A.2 — Desks CRUD

- `DeskResource` — позициониране върху карта чрез **X/Y координати** (проценти)
- Начален подход: `pos_x`, `pos_y` полета във форма (0–100%)
- Удобство: администраторът въвежда приблизителни координати, картата ги визуализира
- **FloorPlanWidget** — прост SVG с overlay на desks (цветни правоъгълници според статуса)

### 5.A.3 — Room CRUD

- `RoomResource` — същото като Desk, но като зала
- capacity, amenities

### 5.A.4 — Desk Assignment (permanent)

- Служител → бюро (дефинитивно)
- `DeskAssignmentResource` — избор на employee + desk + дати
- Валидация: едно бюро = един служител за период
- `end_date = null` = безсрочно
- Ограничение: служител има само едно активно assignment

### 5.A.5 — Desk Booking (hot desking)

- `DeskBookingResource` — employee book-ва desk за определена дата
- Валидация:
  - desk-ът трябва да е `type = hot` ИЛИ да няма permanent assignment за тази дата
  - overlap check: същия desk + същата дата + `status !== cancelled`
  - employee няма друго confirmed booking за същата дата
- Статуси: **confirmed** → може да се **cancell**-не
- Check-in: отметка при пристигане

### 5.A.6 — Room Booking

- `RoomBookingResource` — employee book-ва зала за часови диапазон
- Overlap detection за същата зала + същия time range
- title (име на среща), start/end datetime

### 5.A.7 — Floor Plan Widget (MVP)

- SVG/HTML canvas с background изображение (качената карта)
- Върху нея — цветни правоъгълници за всяко бюро/зала
- Цвят според статуса:
  - 🟢 Зелено = свободно
  - 🔴 Червено = заето (permanent assignment)
  - 🟡 Жълто = резервирано (hot desk booking)
  - ⚪ Сиво = неактивно
- При hover → label + employee name (ако е заето)
- При клик → бутон "Book" за hot desks

### 5.A.8 — Permissions (TeamPermission)

```php
case CreateOffice = 'office:create';
case UpdateOffice = 'office:update';
case DeleteOffice = 'office:delete';
case ManageDesks = 'desk:manage';      // CRUD desks + assignments
case BookDesk = 'desk:book';           // служител
case ManageRooms = 'room:manage';
case BookRoom = 'room:book';
```

---

## 6. Advanced scope (Phase 5.B+)

| Feature | Description | Tech |
|---------|-------------|------|
| **Interactive Canvas Editor** | Drag-drop desks върху картата (не X/Y полета) | react-konva |
| **Live availability** | Реално време — кой къде седи | Laravel Reverb / polling |
| **QR code check-in** | Сканираш QR на бюрото → check-in | QR code generator |
| **No-show auto-release** | Ако няма check-in до 30 мин → освобождава се | Cron job |
| **Waitlist** | Ако няма свободни → join queue + auto-promote | Queue + notification |
| **Calendar sync** | Outlook/Google Calendar iCal feed | iCal export / API |
| **Zone management** | Quiet zone, collaboration zone, focus zone | Zones table |
| **Desk lending (reverse hoteling)** | Permanent desk се освобождава в дни на отсъствие | LeaveRequest интеграция |
| **Analytics** | Utilization %, peak days/часове, floor heatmap | Dashboard |
| **Visitor desk** | Гости → временно бюро без акаунт | Visitor flow |
| **Neighborhoods** | Team zone → определен район за отдела | Group zone |
| **Slack/Teams integration** | `/гдее`, `/book` от чата | Bot integration |
| **Mobile** | React Native / PWA за check-in и quick book | PWA first |

---

## 7. Интеграция с останалите модули

| Модул | Връзка |
|-------|--------|
| **Employees** | Всеки employee има permanent desk assignment + може да book-ва hot desks |
| **Departments** | Dept може да има neighborhood/zone на етажа |
| **Org Chart** | Org chart показва кой на кого се отчита, Desk Booking — физическото разположение |
| **Leave Request** | Ако employee е в отпуск → permanent desk-ът става bookable (desk lending) |
| **AbsenceType** | Само "Vacation" и "Sick Leave" активират desk lending |

---

## 8. Lang keys

```php
// lang/{locale}/app.php
'Office' => 'Офис',
'Offices' => 'Офиси',
'Floor' => 'Етаж',
'Floors' => 'Етажи',
'Floor Plan' => 'План на етажа',
'Desk' => 'Бюро',
'Desks' => 'Бюра',
'Room' => 'Зала',
'Rooms' => 'Зали',
'Permanent' => 'Постоянно',
'Hot Desk' => 'Горещо бюро',
'Visitor' => 'Гост',
'Assignment' => 'Назначение',
'Assignments' => 'Назначения',
'Booking' => 'Резервация',
'Bookings' => 'Резервации',
'Check-in' => 'Пристигане',
'Book Desk' => 'Запази бюро',
'Book Room' => 'Запази зала',
'Cancel Booking' => 'Отмени резервация',
'Available' => 'Свободно',
'Occupied' => 'Заето',
'Reserved' => 'Резервирано',
'Who\'s in the office' => 'Кой е в офиса',
'Free desks today' => 'Свободни бюра днес',
'Capacity' => 'Капацитет',
'Amenities' => 'Удобства',
'Quiet Zone' => 'Тиха зона',
'Collaboration Zone' => 'Зона за колаборация',
'Focus Zone' => 'Зона за фокус',
'No Show' => 'Не се яви',
'Reverse Hoteling' => 'Обратно резервиране',
'Desk Lending' => 'Отдаване на бюро',
```

---

## 9. Какво НЕ правим (out of scope)

- Parking spot booking — ⏳ може по-късно като extension на desks
- Office hardware (lockers, monitors) — ⏳ бъдещо
- Biometric/Face recognition check-in — ⏳ бъдещо
- Elevator/badge integration — ⏳ enterprise
- Cleaning crew workflow — ⏳ facility management
- Energy management (HVAC integration) — ⏳ enterprise

---

_Последна актуализация: 2026-06-02_

# Неизвестни / Отворени въпроси

Този файл съдържа всички неизвестни, които сме идентифицирали.
Всяко unknown има `resolve_at` — към коя фаза/стъпка се отнася.

---

## unknown-subdomain-handling.md

**Въпрос:** Как тестваме локално и работим с поддомейни (`company.hrapp.app`)?

**resolve_at:** Фаза 2 (Expansion)

**Зависимости:** Docker networking, DNS за локална разработка.

---

## unknown-multi-company-user.md

**Въпрос:** Един потребител може да бъде в няколко компании?
Как разделяме данните, ако един User има достъп до няколко Team-a?

**resolve_at:** Фаза 2 (Expansion)

**Зависимости:** Session management, team switching UI, authorization.

---

## unknown-team-scoping.md

**Въпрос:** Как точно работи scoping-а чрез Team-ове?
Мениджър вижда само заявките на своя Team?
Employee-ите от един отдел виждат ли колегите си?

**resolve_at:** Фаза 2 (Expansion)

**Зависимости:** Roles & permissions strategy.

---

## unknown-billing-tiers.md

**Въпрос:** Billing интеграция (Stripe), pricing tiers, subscription management.
Как се случва преходът от free → paid? Какви са нивата?

**resolve_at:** Фаза 2 (Expansion)

**Зависимости:** Stripe/аналог, webhook handling, tenant provisioning.

---

## unknown-database-per-tenant-migration.md

**Въпрос:** Как, кога и с каква стратегия мигрираме от една база към database-per-tenant?

**resolve_at:** Фаза 2 (Expansion)

**Зависимости:** `spatie/laravel-multitenancy`, data migration strategy, zero-downtime approach.

---

## unknown-api-integrations.md

**Въпрос:** API endpoints за външни интеграции?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-testing-strategy.md

**Въпрос:** Как тестваме? Feature tests, tenancy tests?

**resolve_at:** Фаза 1 + Фаза 2

---

## unknown-file-uploads.md

**Въпрос:** Качване на файлове (аватари, документи)?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-exports-reports.md

**Въпрос:** CSV/Excel export, reports & analytics?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-notifications.md

**Въпрос:** Slack/Teams/email notification интеграции?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-time-tracking.md

**Въпрос:** Time tracking модул?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-performance-reviews.md

**Въпрос:** Performance reviews модул?

**resolve_at:** Фаза 2 (Expansion)

---

## unknown-recruitment.md

**Въпрос:** Recruitment/job postings модул?

**resolve_at:** Фаза 2 (Expansion)

---

# Document Hub & AI Content Generation — Plan

**Дата:** 2026-06-02
**Статус:** ⏳ Планиране (идея)
**Връзка:** [PLAN.md](PLAN.md) → Фаза 6

---

## 1. Feature Overview

Централизирано хранилище за HR документи + AI инструменти за генериране на съдържание.

**Две основни групи функционалности:**

| Група | Описание |
|-------|----------|
| **Document Hub** | Централно хранилище за всички HR документи — длъжностни характеристики, договори, политики, onboarding документи. Категоризация, версии, placeholders. |
| **AI Content Studio** | AI-базирано генериране на HR документи (длъжностни характеристики, job ad text, offer letters, performance reviews) и публикуване към job boards. |

---

## 2. Competitive Benchmark

### Document Management

| Feature | BambooHR | Personio | **MVP** | **Advanced** |
|---------|----------|----------|---------|--------------|
| Central document repository | ✅ | ✅ | **✅** | ✅ |
| Document categories | ✅ | ✅ | **✅** | ✅ |
| Document templates with placeholders | ❌ | ✅ | **✅** | ✅ |
| Bulk document creation | ❌ | ✅ | ❌ | ✅ |
| E-signature (DocuSign) | ✅ | ✅ | ❌ | ✅ |
| Document versioning | ❌ | ❌ | ❌ | ✅ |
| Document retention policies | ❌ | ✅ | ❌ | ✅ |
| Employee self-service upload | ✅ | ✅ | **✅** | ✅ |
| Bulk upload/export | ✅ | ✅ | ❌ | ✅ |
| Permission-based access | ✅ | ✅ | **✅** | ✅ |
| Document preview (in-browser) | ✅ | ✅ | ❌ | ✅ |

### AI Content Generation

| Feature | BambooHR AI | Personio AI | Open Source | **MVP** | **Advanced** |
|---------|-------------|-------------|-------------|---------|--------------|
| AI JD generation from prompt | ✅ (Pro+) | 🔮 planned | ✅ (many) | **✅** | ✅ |
| AI JD enhancement/optimization | ❌ | ❌ | ✅ | ❌ | ✅ |
| AI title suggestions | ❌ | ❌ | ✅ | ❌ | ✅ |
| AI offer letter generation | ❌ | ❌ | ✅ | ❌ | ✅ |
| AI performance review drafts | ❌ | ❌ | ✅ | ❌ | ✅ |
| Job ad text (LinkedIn/ATS version) | ❌ | ❌ | ✅ | **✅** | ✅ |
| AI outreach messages | ❌ | ❌ | ✅ | ❌ | ✅ |
| AI eNPS summarization | ✅ | ❌ | ❌ | ❌ | ✅ |
| Bias check / inclusive language | ❌ | ❌ | ✅ | ❌ | ✅ |
| Multi-language JD generation | ❌ | ❌ | ❌ | ❌ | ✅ |

**Sources:**
- BambooHR AI Principles — https://www.bamboohr.com/about-bamboohr/ai-principles
- BambooHR Pricing (ATS + AI tiers) — https://www.pin.com/blog/bamboohr-pricing/
- Personio Document Management — https://support.personio.de/hc/en-us/articles/6977125477405-Manage-document-categories-and-templates
- Personio Templates — https://www.personio.com/product/templates/
- Personio Bulk Document Creation — https://community.personio.com/product-spotlight-133/bulk-document-creation
- Skills HR (Claude Code) — https://github.com/Gonzih/skills-hr
- TalentPulse AI — https://github.com/Atharva-System/intelliHR_AI
- HR Document Generator (Jinja2 + OpenAI) — https://github.com/lifeofgurpreet/hr-document-generator
- JobDescriptions.fyi — https://github.com/eajr/jobdescriptions-fyi

---

## 3. Database Schema

```sql
-- Всички таблици са tenant-scoped (team_id FK)

document_categories:
  id              bigint PK
  team_id         bigint FK → teams
  name            varchar(255)
  slug            varchar(255)          // "job-descriptions", "contracts", "policies"
  description     text nullable
  parent_id       bigint nullable FK → document_categories (tree)
  icon            varchar(64) nullable   // icon name
  sort            integer
  is_system       boolean default false  // system category (cannot be deleted)
  created_at, updated_at

document_templates:
  id              bigint PK
  team_id         bigint FK → teams
  category_id     bigint FK → document_categories
  name            varchar(255)          // "Длъжностна характеристика - шаблон"
  description     text nullable
  file_path       varchar(2048)         // .docx/.odt файл с placeholders
  content_html    longtext nullable     // WYSIWYG редактор версия
  placeholders    json nullable         // [{key: "employee_name", label: "Име", type: "system|free"}]
  language        varchar(5) default 'bg'
  is_active       boolean default true
  version         integer default 1
  created_at, updated_at

documents:
  id              bigint PK
  team_id         bigint FK → teams
  category_id     bigint FK → document_categories
  template_id     bigint nullable FK → document_templates
  title           varchar(255)          // "Длъжностна характеристика - Senior PHP Developer"
  employee_id     bigint nullable FK → employees
  file_path       varchar(2048)         // generated .pdf/.docx
  file_type       varchar(50)           // "pdf", "docx", "txt"
  file_size       integer               // bytes
  content_html    longtext nullable     // rendered HTML version
  status          enum(draft,final,archived)
  metadata        json nullable         // {generated_by: "ai", model: "gpt-4o", job_title: "..."}
  signed_by       json nullable         // [{employee_id, signed_at}]
  signed_at       timestamp nullable
  created_by      bigint FK → users
  created_at, updated_at

job_descriptions:
  id              bigint PK
  team_id         bigint FK → teams
  document_id     bigint nullable FK → documents   // връзка към generated document
  title           varchar(255)                      // "Senior PHP Developer"
  department_id   bigint nullable FK → departments
  reports_to_id   bigint nullable FK → employees    // на кого се отчита
  employment_type enum(full_time,part_time,contract,b2b,freelance)
  location        varchar(255) nullable
  min_salary      decimal(10,2) nullable
  max_salary      decimal(10,2) nullable
  currency        varchar(3) default 'BGN'
  summary         text
  responsibilities json
  requirements    json
  nice_to_have    json nullable
  benefits        json nullable
  skills          json nullable
  is_active       boolean default true
  version         integer default 1
  created_by      bigint FK → users
  created_at, updated_at

job_ads:
  id              bigint PK
  team_id         bigint FK → teams
  job_description_id bigint FK → job_descriptions
  platform        varchar(50)           // "linkedin", "jobs.bg", "dev.bg", "github"
  headline        varchar(255)          // LinkedIn version (250 chars)
  body            text                  // ATS optimized version
  outreach_text   text nullable         // за passive candidate sourcing
  status          enum(draft,published,closed)
  published_url   varchar(2048) nullable
  published_at    timestamp nullable
  created_at, updated_at

ai_generation_log:
  id              bigint PK
  team_id         bigint FK → teams
  user_id         bigint FK → users
  document_type   varchar(100)          // "job_description", "offer_letter", "job_ad"
  input_prompt    text
  output_content  longtext
  model           varchar(100)          // "gpt-4o", "claude-3-opus"
  tokens_used     integer
  duration_ms     integer
  created_at
```

---

## 4. Architecture

```
app/
├── Models/
│   ├── DocumentCategory.php
│   ├── DocumentTemplate.php
│   ├── Document.php
│   ├── JobDescription.php
│   ├── JobAd.php
│   ├── AiGenerationLog.php
│   └── ... (съществуващи)
│
├── Filament/Company/
│   ├── Resources/
│   │   ├── DocumentCategoryResource.php
│   │   ├── DocumentTemplateResource.php
│   │   ├── DocumentResource.php
│   │   ├── JobDescriptionResource.php
│   │   └── JobAdResource.php
│   └── Widgets/
│       └── RecentDocumentsWidget.php
│
├── Services/
│   ├── DocumentGeneratorService.php        // template rendering + placeholder fill
│   └── AiContentService.php               // OpenAI/Llama API calls
│
├── Jobs/
│   ├── GenerateDocumentJob.php             // async generation
│   └── PublishJobAdJob.php                 // publish to job board
│
├── Rules/
│   └── PlaceholderRule.php                 // валидация на placeholders
│
└── Enums/
    ├── DocumentStatus.php                  (draft, final, archived)
    ├── EmploymentType.php                  (full_time, part_time, contract, b2b, freelance)
    └── JobAdPlatform.php                   (linkedin, jobs.bg, dev.bg)
```

---

## 5. MVP scope (Phase 6.A — Document Hub)

### 6.A.1 — Document Categories

- CRUD дърво от категории (parent_id за подкатегории)
- Системни категории (не се трият):
  - `job-descriptions` — Длъжностни характеристики
  - `contracts` — Трудови договори
  - `policies` — Вътрешни правила
  - `onboarding` — Onboarding документи
  - `performance` — Performance reviews
  - `other` — Други

### 6.A.2 — Document Templates

- Upload на `.docx` / `.odt` шаблон с placeholders
- Placeholders: `{{employee_name}}`, `{{job_title}}`, `{{department}}`, `{{salary}}`
- Два типа placeholders:
  - **System** — auto-fill от employee/team data
  - **Free** — потребителят попълва ръчно
- Preview на template (download)
- Assign to category

### 6.A.3 — Documents

- Upload на документ → категория
- Generate от template → избор на employee → auto-fill placeholders → download PDF
- Status workflow: draft → final → archived
- Свързване с employee (който employee има този документ)
- Permission-based достъп (admin вижда всичко, employee — своите)

### 6.A.4 — Employee self-service

- Employee вижда своите документи (договор, длъжностна характеристика)
- Download PDF
- Upload на собствени документи (в assigned категории)

---

## 6. MVP scope (Phase 6.B — AI Content Studio)

### 6.B.1 — AI Job Description Generator

- Input: job title, department, location, employment type, key requirements
- AI generates: summary, responsibilities, requirements, nice-to-have, benefits
- Save като `JobDescription` → свързан с `Document` в категория "Длъжностни характеристики"
- Output формат:
  - **ATS версия** (500-700 думи, detailed)
  - **Short version** (LinkedIn, 250 думи)
- Multi-language: prompt-ът взима езика от `app()->getLocale()`

**Технология:**
- Laravel + OpenAI API (GPT-4o) / Claude API / Ollama (локално)
- Structured output (JSON mode) за консистентни резултати
- Prompt template с few-shot примери за HR контекст
- Асинхронно генериране (queue job) за дълги documents

**Примерен flow:**
```
User: "Generate JD for Senior PHP Developer"
  → AI генерира: title + summary + responsibilities + requirements + benefits
  → Създава JobDescription record
  → Създава Document record в category "job-descriptions"
  → Показва preview за редакция
  → User финализира → status = final
```

### 6.B.2 — AI Job Ad Generator

- От съществуващ `JobDescription` → генерира job ad за конкретна платформа
- LinkedIn версия (250 chars headline + 500 chars body)
- Jobs.bg / Dev.bg версия
- Outreach text за passive candidate sourcing
- Bias check (inclusive language)

### 6.B.3 — AI Document History

- `AiGenerationLog` — пълен log на всички AI генерации
- Позволява re-generate със същия prompt
- Лог на tokens + cost

---

## 7. Advanced scope (Phase 6.C+)

| Feature | Описание |
|---------|----------|
| **E-signature (DocuSign)** | Интеграция за подписване на документи |
| **Bulk document creation** | Generate documents за multiple employees наведнъж |
| **Document versioning** | Пълна история на версиите на всеки документ |
| **Document retention** | Auto-archive/delete според политики |
| **WYSIWYG editor** | In-browser редактор за документи (без Word) |
| **AI Offer Letter** | Генериране на offer letter + адаптация |
| **AI Performance Review** | Draft-ване на performance review от raw notes |
| **AI Interview Kit** | Генериране на interview questions + scoring rubric |
| **AI eNPS summarization** | Обобщение на eNPS feedback |
| **Slack/Teams integration** | Request document via чат |
| **Advanced RAG** | Търсене в документи с AI (като HR Bot) |
| **Multi-language AI** | JD на 5+ езика |
| **Job board publishing** | Auto-post към LinkedIn, Jobs.bg, Dev.bg чрез API |
| **PDF/A archiving** | Long-term archival формат |
| **QR code на документ** | Верификация на автентичност |

---

## 8. AI Architecture

```
┌─────────────────────────────────────────────┐
│           AiContentService                   │
│  (Laravel Service - injectable)              │
├─────────────────────────────────────────────┤
│  + generateJobDescription(prompt): JobDesc   │
│  + generateJobAd(jd, platform): JobAd        │
│  + enhanceJobDescription(jd): JobDesc        │
│  + generateOfferLetter(employee): Document   │
│  + biasCheck(text): Report                   │
└──────────────────┬──────────────────────────┘
                   │
    ┌──────────────┴──────────────┐
    │         HTTP / Queue        │
    └──────────────┬──────────────┘
                   │
    ┌──────────────┴──────────────┐
    │      AI Provider Layer       │
    ├─────────────────────────────┤
    │  OpenAIAdapter               │
    │  ClaudeAdapter               │
    │  OllamaAdapter (local)       │
    └─────────────────────────────┘
```

**Prompt engineering example (BG):**
```
Ти си HR експерт с 15+ години опит в създаването на длъжностни характеристики.
Генерирай длъжностна характеристика за позиция "{job_title}" в отдел "{department}".
Изисквания:
- Длъжността е на {employment_type}
- Локация: {location}
- Екипът се отчита на: {reports_to}
- Език на документа: {locale}

Формат на JSON изхода:
{
  "summary": "...",
  "responsibilities": ["...", "..."],
  "requirements": ["...", "..."],
  "nice_to_have": ["...", "..."],
  "benefits": ["...", "..."],
  "ats_version": "... (500-700 думи)",
  "linkedin_headline": "... (макс 250 символа)",
  "linkedin_body": "... (макс 2000 символа)"
}
```

---

## 9. Permissions (TeamPermission)

```php
case ManageDocumentCategories = 'document:manage_categories';
case ManageDocumentTemplates = 'document:manage_templates';
case CreateDocument = 'document:create';
case ViewDocument = 'document:view';           // own + team
case ViewAllDocuments = 'document:view_all';   // admin/owner
case SignDocument = 'document:sign';
case ArchiveDocument = 'document:archive';
case UseAiGenerator = 'document:ai_generate';
case PublishJobAd = 'document:publish_job_ad';
```

**TeamRole mapping:**
- `Owner` → всичко
- `Admin` → всичко без `publish_job_ad`
- `Editor` → `document:create`, `document:view`, `document:ai_generate`
- `Viewer` → `document:view` (своите документи)

---

## 10. Lang keys

```php
// lang/{locale}/app.php
'Documents' => 'Документи',
'Document Categories' => 'Категории документи',
'Document Templates' => 'Шаблони за документи',
'Job Descriptions' => 'Длъжностни характеристики',
'Job Ads' => 'Обяви за работа',
'Generate with AI' => 'Генерирай с ИИ',
'AI Generator' => 'ИИ Генератор',
'ATS Version' => 'ATS версия',
'LinkedIn Version' => 'LinkedIn версия',
'Placeholder' => 'Плейсхолдър',
'Placeholders' => 'Плейсхолдъри',
'System Placeholder' => 'Системен плейсхолдър',
'Free Placeholder' => 'Свободен плейсхолдър',
'Preview' => 'Преглед',
'Download PDF' => 'Изтегли PDF',
'Sign Document' => 'Подпиши документ',
'Draft' => 'Чернова',
'Final' => 'Финал',
'Archived' => 'Архивиран',
'Published' => 'Публикуван',
'Summary' => 'Резюме',
'Responsibilities' => 'Отговорности',
'Requirements' => 'Изисквания',
'Nice to Have' => 'Желателно',
'Benefits' => 'Предимства',
'Full Time' => 'Пълно работно време',
'Part Time' => 'Непълно работно време',
'Contract' => 'Договор',
'B2B' => 'B2B',
'Freelance' => 'Фрийланс',
```

---

## 11. Файл структура

```
app/
├── Services/
│   ├── DocumentGeneratorService.php
│   │   - generateFromTemplate(template, employee, placeholders)
│   │   - mergePlaceholders(content, data)
│   │   - exportToPdf(html): file
│   │
│   └── AiContentService.php
│       - generateJobDescription(prompt): array
│       - generateJobAd(jd, platform): string
│       - enhanceText(text, context): string
│       - biasCheck(text): array
│
├── Adapters/
│   └── AiProvider.php (interface)
│       ├── OpenAiAdapter.php
│       ├── ClaudeAdapter.php
│       └── OllamaAdapter.php
│
├── Jobs/
│   ├── GenerateDocumentFromAiJob.php
│   └── PublishJobAdToBoardJob.php
│
└── Console/Commands/
    └── TestAiProvider.php  // php artisan ai:test
```

---

## 12. Implementation order (препоръка)

| Стъпка | Какво | Зависи от |
|--------|-------|-----------|
| **1** | DocumentCategories CRUD | Фаза 2 (Leave) — team_id scoping |
| **2** | DocumentTemplates — upload + placeholders | Стъпка 1 |
| **3** | Documents — upload + generate от template | Стъпка 2 + Employees |
| **4** | Employee self-service (view own docs) | Стъпка 3 |
| **5** | JobDescription model + CRUD | Стъпка 3 + Фаза 4 (Departments) |
| **6** | AI integration (OpenAI API key в config) | Стъпка 5 |
| **7** | AI Job Description Generator UI | Стъпка 6 |
| **8** | AI Job Ad Generator + bias check | Стъпка 7 |
| **9** | AiGenerationLog + monitoring | Стъпка 8 |

---

## 13. Референции

- **Personio Document Management** — https://support.personio.de/hc/en-us/articles/6977125477405
- **Personio Document Templates** — https://support.personio.de/hc/en-us/articles/115002763969
- **Personio Bulk Document Creation** — https://community.personio.com/product-spotlight-133/bulk-document-creation
- **BambooHR AI Features** — https://www.bamboohr.com/about-bamboohr/ai-principles
- **Skills HR (Claude Code)** — https://github.com/Gonzih/skills-hr
- **TalentPulse AI** — https://github.com/Atharva-System/intelliHR_AI
- **HR Document Generator (Jinja2+OpenAI)** — https://github.com/lifeofgurpreet/hr-document-generator
- **HR Bot v8 (CrewAI + Bedrock)** — https://github.com/ARYDESTROYER/HR_BOT_V1
- **JobDescriptions.fyi** — https://github.com/eajr/jobdescriptions-fyi

---

## 14. Моето мнение

**Това е най-добрата "следваща функционалност"**, защото:

1. **Естествено продължение** — след като имаме Employees + Departments + Leave, следващата стъпка е да документираме ролите и отговорностите
2. **AI е конкурентно предимство** — Personio го нямат официално (само "планирано"), BambooHR го имат само в Pro/Elite
3. **Свързва всичко**: Employees → Job Descriptions → Org Chart → Hiring → Onboarding
4. **Job Ad generator** е допълнителен бонус — LinkedIn/Jobs.bg формати
5. **Open source AI (Ollama)** = 0 оперативни разходи за inference

---

_Последна актуализация: 2026-06-02_

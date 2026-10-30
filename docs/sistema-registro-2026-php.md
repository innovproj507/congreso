# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Registration system for **Congreso Panameño de Pediatría 2026** — handles participant registration, dynamic pricing, multi-method payments (ACH, Yappy, credit card), sponsor QR portals, and PDF receipt generation. All UI text and error messages are in Spanish.

## Setup

```bash
# Install PHP dependencies
composer install

# Database: import the schema into MySQL
# Run in MySQL: source api/database.sql
```

**Configuration uses three layers (evaluated in order):**

1. `.env` file (copy from `.env.example`) — `SITE_URL`, `DB_*`, `CROEM_API_KEY`, `SMTP_USER`, `SMTP_PASS`
2. `settings` DB table — runtime config editable from the admin panel; loaded by `loadDynamicSettings()` in [api/config.php](api/config.php)
3. Hardcoded constants in [api/config.php](api/config.php) — fallback defaults

`SITE_URL` from `.env` always wins over the DB setting (it's deployment config, not app config).

## Utility / Diagnostic Scripts

Run directly via browser or CLI during development:

| Script | Purpose |
|---|---|
| `smtp_test.php` / `smtp_diag.php` / `fix_smtp.php` | Test and fix SMTP configuration |
| `check_schema.php` / `check_settings_schema.php` | Validate database schema |
| `dump_settings.php` | Inspect current settings table |
| `view_receipt.php` | Preview PDF receipt generation |
| `mantenimiento.php` | Toggle maintenance mode |
| `create_admin.php` | Bootstrap first admin user |
| `check_env.php` | Dump resolved `.env` values |
| `test_insert.php` / `test_query.php` / `test-sponsor-email.php` | Ad-hoc DB/email smoke tests |

**Migration scripts** (each is idempotent — safe to re-run; run once against production after pulling schema-changing commits):

| Script | Adds/creates |
|---|---|
| `migrate_production.php` / `migrate_produccion.php` | Two separate, overlapping production column patchers — check both when a "column not found" error appears in prod |
| `migrate_checkin.php` | `check_in_code` + check-in support columns on `registrations` |
| `migrate_evento_tables.php` | Parallel multi-tenant scaffold: `tenants`, `events`, synced `users` — see [Multi-event scaffolding](#multi-event-scaffolding-not-yet-active) below |
| `migrate_etiqueta_settings.php` | `etiqueta_settings` (QR base URL config for badge printing) |
| `migrate_trivia.php` | `trivia_questions` / `trivia_answers`, seeded with ~40 questions |
| `fix_checkins_table.php` | Rebuilds legacy `checkins` table from `agenda_inscripciones` |
| `fix_sponsors_event_id.php` | Adds `sponsors.event_id` (defaults to `1`) |
| `fix_db.php` / `fix_views.php` / `fix_views_definer.sql` | Schema patches; `fix_views.php` strips `DEFINER` clauses from views for portability across environments |
| `generate_qr_codes.php` | Bulk-generates sponsor/ticket QR codes |

## Architecture

**Stack:** Vanilla PHP (no framework), MySQL, TailwindCSS (CDN), Vanilla JS, Dompdf for PDFs.

### Entry points

- [index.php](index.php) — Public registration form (~1100 lines)
- [admin/](admin/) — Full admin panel (login, dashboard, registrations, reports, settings, users, sponsors, agenda, check-in, certificates, surveys, trivia)
- [sponsor.html](sponsor.html) + [js/sponsor.js](js/sponsor.js) — Sponsor QR portal
- [patrocinador-pago.php](patrocinador-pago.php) — Public sponsor payment form; posts to `api/sponsor-pago-publico.php`
- [ticket.php](ticket.php) — Public virtual ticket (`/ticket/{code}`) showing a participant's QR for session check-in, rendered via `api/checkin/qr.php`
- [agenda/index.php](agenda/index.php) — Public schedule browser; attendees self-register for talks via `agenda/api/`
- [certificado/index.php](certificado/index.php) — Public role-specific attendance certificate generator (requires `estado_pago = 'confirmado'`)
- [constancia/index.php](constancia/index.php) — Public generic attendance letter (simpler than `certificado/`, keyed by cédula only)
- [encuesta/index.php](encuesta/index.php) — Public post-event survey (one submission per registration)
- [spp/index.php](spp/index.php) — Legacy Joomla-based version; not actively developed

### Shared PHP classes (`includes/`)

These are used by both the admin panel and the public API:

- **`Database.php`** — Singleton PDO wrapper (`Database::getInstance()`). UTF-8MB4, `PDO::ATTR_EMULATE_PREPARES = false`. Exposes `fetchAll()`, `fetchOne()`, `execute()`, `lastInsertId()`, and transaction helpers. Prefer this in all new code.
- **`Auth.php`** — Admin session management (8-hour lifetime, stored in `admin_sessions` table). Tracks login attempts per user; auto-locks account after 5 failures for 15 minutes. Uses dummy hash verify to prevent timing attacks on unknown usernames. Call `Auth::requireAuth('viewer')` at the top of every admin page.
- **`Security.php`** — CSRF token generation/validation (2-hour expiry, multi-tab safe), rate limiting by IP+action stored in `rate_limits` DB table, security headers, input sanitization.

**Admin role hierarchy (lowest → highest):** `viewer` < `editor` < `admin` < `super_admin`. Pass the minimum required role to `Auth::requireAuth()`. Use `Auth::hasRole()` for inline role checks within a page.

All admin pages must begin with:
```php
require_once __DIR__ . '/../includes/admin_bootstrap.php';
```

### Dual DB / CSRF systems (important)

Two parallel implementations coexist for historical reasons:

| | Admin pages / new code | Public API (`api/register.php`) |
|---|---|---|
| **DB** | `Database::getInstance()` | `getDBConnection()` in `api/config.php` |
| **CSRF** | `Security::generateCSRF()` / `Security::validateCSRF()` | `generateCSRFToken()` / `validateCSRFToken()` in `api/config.php` |
| **Sanitize** | `Security::sanitize()` — applies `htmlspecialchars` + `strip_tags` | `sanitizeInput()` — trim only; data stored raw and escaped at output |

Do not mix these across boundaries. New code should use the OO classes.

### Backend (`api/`)

- [api/config.php](api/config.php) — Three-tier config loader, all constants, pricing dates, CROEM gateway config, `smtpSend()`, `generateReceiptPDF()`, `sendConfirmationEmail()`, `processCroemPayment()`
- [api/register.php](api/register.php) — Public registration POST endpoint: validates CSRF, validates + re-prices server-side, writes to DB, sends confirmation email, generates PDF receipt
- [api/check_sponsor.php](api/check_sponsor.php) — Validates sponsor tokens and returns quota status
- [api/admin-registrations.php](api/admin-registrations.php) — Admin API for listing/editing registrations
- [api/admin-sponsor-cobro.php](api/admin-sponsor-cobro.php) — Admin API: charges a sponsor via CROEM/ACH/cash and creates the sponsor record atomically
- [api/sponsor-pago-publico.php](api/sponsor-pago-publico.php) — Public-facing sponsor payment endpoint (no admin auth); uses `Security::` classes
- [api/save_settings.php](api/save_settings.php) — Admin API for updating the `settings` DB table
- [api/database.sql](api/database.sql) — Base schema (live DB has additional columns added via migrations)
- [api/checkin/](api/checkin/) — QR-based per-session check-in PWA backend: `_bootstrap.php`/`_auth.php` (shared setup/staff auth), `login.php`, `events.php`, `sessions.php`, `attendee.php` (lookup by `check_in_code`), `checkin.php` (upserts `agenda_inscripciones.checked_in`), `qr.php` (PNG QR generation via chillerlan/php-qrcode), `ping.php`
- [agenda/api/](agenda/api/) — Public self-service talk registration: `inscribir.php` / `cancelar.php` / `verificar.php` / `mis-charlas.php`, all operating on `agenda_inscripciones`

### Frontend (`js/`)

JS modules follow an object-oriented pattern with named classes:

- `PricingManager` ([js/pricing.js](js/pricing.js)) — Price lookup per participant type, presale cutoff logic, benefit/sponsor override (price → $0 or $1 for PRUEBA)
- `FormValidator` ([js/form-validation.js](js/form-validation.js)) — Client-side validation for `#registration-form`
- `PaymentHandler` ([js/payment.js](js/payment.js)) — Payment method switching, file upload for ACH/Yappy vouchers
- `CroemIntegration` ([js/croem-integration.js](js/croem-integration.js)) — Client-side CROEM sandbox tokenizer
- [js/main.js](js/main.js) — Orchestrates the above; wires events, calls `setParticipantType()` → pricing update → payment UI

### Database schema

Core tables: `registrations`, `sponsors`, `payment_confirmations`, `audit_log`, `admin_users`, `admin_sessions`, `settings`, `rate_limits`, `agenda_charlas`, `agenda_inscripciones`

Subsystem tables (added via the migration scripts above): `checkins` (legacy, rebuilt from `agenda_inscripciones` by `fix_checkins_table.php`), `etiqueta_settings`, `encuesta_respuestas`, `encuesta_invitaciones`, `certificado_invitaciones`, `trivia_questions`, `trivia_answers`

Views: `registration_stats`, `dashboard_kpi`, `sponsor_stats`

Key enums:
- `tipo_participante`: `socio_activo`, `socio_emerito`, `no_socio`, `medico_general`, `residente_estudiante`, `enfermera_profesional`, `junta_directiva`, `conferencista`, `comite_cientifico`
- `metodo_pago`: `ach`, `yappy`, `tarjeta`
- `estado_pago`: `pendiente`, `confirmado`, `cancelado`

The `sponsors` table (live schema) has: `id`, `name`, `token`, `quota`, `used`, `active`, `monto_pagado`, `metodo_pago_sponsor`, `referencia_pago`, `fecha_pago`. The `api/database.sql` file reflects an older schema; run migrations for the current structure.

Every write to `registrations` is diffed and logged to `audit_log` as JSON.

### Payment flow

1. **ACH / Yappy** — User uploads proof image → stored in `uploads/` → admin reviews in panel → manually sets `estado_pago = 'confirmado'`
2. **Credit card (CROEM)** — `CroemIntegration` tokenizes client-side → token posted to `api/register.php` → server charges synchronously via CROEM SOAP 1.1 API → registration created only on success
3. **Free / sponsored** — Price $0 when: participant type is `socio_emerito`, `junta_directiva`, `conferencista`, or `comite_cientifico`; or a valid sponsor token is redeemed; or benefit codes are applied

### Pricing logic

Two tiers only (presale and regular). Presale cutoff: **April 30, 2026 @ 23:59:59 Panama time**. Prices are set in [js/pricing.js](js/pricing.js) and **re-validated server-side** in [api/register.php](api/register.php) — never trust the client-submitted price.

**Special benefit tokens:**
- `JORNADA2026` — Single-day ticket ($75); shows day selector in form
- `PATROCINIO2026` — Sponsored registration at `socio_activo` price; requires sponsor name field
- `PRUEBA` — $1.00 test charge for validating the CROEM gateway in production
- Any other value — looked up against `sponsors` table; sets price to $0 if `used < quota`

### Sponsor quota system

Sponsors have a `quota` and a `used` counter in the `sponsors` table. `api/check_sponsor.php` validates a token and confirms `used < quota`. On successful registration with a sponsor code, `used` is incremented and `registration.sponsor_code` is set. Over-redemption is blocked server-side.

**Sponsor cobro flow:** Admins use `admin/sponsor-cobro.php` to charge a sponsor via card (CROEM), ACH, or cash and simultaneously create the sponsor record with their token and quota. This is the primary way sponsor accounts are created.

### Admin panel (`admin/`)

Full CRUD interface with:
- `index.php` — Login with rate limiting (10 attempts / 15 min per IP)
- `dashboard.php` — KPI metrics from `dashboard_kpi` view
- `registrations.php` / `registration-detail.php` — List, filter, view, edit registrations; confirm payments
- `sponsors.php` / `sponsor-cobro.php` — Manage sponsor codes and quotas; charge sponsors via card
- `agenda.php` — Manage congress schedule (`agenda_charlas` table): add/edit/toggle sessions
- `agenda-vista.php` — Read-only view of the schedule
- `agenda-inscripciones.php` — Per-session attendee list from `agenda_inscripciones` table
- `checkin.php` / `checkin-detail.php` — Check-in dashboard: KPIs and per-session check-in counts/detail, joining `agenda_charlas` + `agenda_inscripciones`
- `certificados.php` / `certificado-correo.php` — Configure certificate settings (`cert_*` keys in `settings`) and signature images (`certificado/firmas/`); bulk-email certificate links (tracked in `certificado_invitaciones`)
- `encuesta-correo.php` / `export-encuesta.php` / `reporte-encuesta.php` — Email survey invitations (tracked in `encuesta_invitaciones`), export responses to XLSX (PhpSpreadsheet), view aggregate averages
- `trivia-preguntas.php` — CRUD for `trivia_questions` (multiple choice, options A–D, `correct_idx`); deleting a question cascades to `trivia_answers`
- `etiquetas.php` — Name-badge/label printing: filterable registration list with QR codes (uses `etiqueta_settings.qr_base_url`), `?print=1` for bulk print layout
- `reporte-*.php` (`crucero`, `duplicados`, `emails-problematicos`, `incompletos`, `participantes`, `primeros`) — Specialized data-quality and analytics reports
- `reports.php` — Data export and analytics
- `settings.php` — Edit `settings` DB table via `api/save_settings.php`
- `users.php` — Admin user management
- `partials/` — Shared header, footer, 403 templates

The `ADMIN_CONTEXT` constant is set by `admin_bootstrap.php` so API error responses can be formatted differently for the admin panel vs the public form.

### Attendee-facing subsystems

Several public, unauthenticated flows exist alongside registration, all gated on `registrations.estado_pago = 'confirmado'` (or cédula lookup) rather than admin auth:

- **Check-in / QR ticket** — [ticket.php](ticket.php) shows each participant a QR (rendered by `api/checkin/qr.php`) encoding their `check_in_code`. Congress staff scan it in the check-in PWA (`api/checkin/`), which resolves the attendee and writes attendance to `agenda_inscripciones` (`checked_in`, `checked_in_at`), logged to `audit_log`.
- **Agenda self-registration** — [agenda/index.php](agenda/index.php) lets attendees browse talks/workshops by day and room with live seat counts, and sign up or cancel via `agenda/api/` (writes to `agenda_inscripciones`, same table the admin agenda pages read).
- **Certificates vs. constancias** — `certificado/index.php` produces a role-specific attendance certificate (text varies for conferencista, comité científico, etc.); `constancia/index.php` produces a simpler generic "Constancia de Participación" letter. Both pull branding/dates/signatures from `settings` (`cert_*` keys).
- **Encuesta (survey)** — [encuesta/index.php](encuesta/index.php) self-creates the `encuesta_respuestas` table on first load if missing and enforces one submission per `registration_id`.
- **Trivia** — Backed by `trivia_questions` / `trivia_answers`; questions are managed in `admin/trivia-preguntas.php`. There is no public `trivia/` front end yet in this repo — a public submission endpoint would need to be added before this is usable by attendees.

### Multi-event scaffolding (not yet active)

`migrate_evento_tables.php` and `fix_sponsors_event_id.php` introduce a **separate, parallel multi-tenant schema** (`tenants`, `events`, an `event_id`-scoped `users` table synced from `admin_users`) intended for a future multi-congress rewrite. Currently this only manifests as a `sponsors.event_id` column defaulted to `1` — the core registration/agenda/check-in flow is still single-event and does not read `event_id` anywhere. Don't assume multi-event support exists in `api/config.php` or the main flows; treat this scaffolding as inactive until it's actually wired in.

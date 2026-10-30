# Guía de revisión del proyecto (para otro revisor / otro Claude)

Objetivo: que una segunda persona o agente revise **seguridad, corrección y diseño** de este proyecto antes de publicarlo. No cambies nada sin explicar el motivo; prioriza hallazgos reales sobre estilo. Responde en español.

## 1. Qué es

Sistema de inscripción y cobro del **LV Congreso Nacional de Pediatría 2027 (Sociedad Panameña de Pediatría, 17–19 de junio de 2027)**. Son **dos proyectos independientes** que se hablan por HTTP:

| Proyecto | Ruta local | Tecnología | Función |
|---|---|---|---|
| Landing + Eventos | `C:\laragon\www\congreso` | PHP 8.2, CodeIgniter 4.7, MySQL 8 (`spp_congreso_2027`), Tailwind 4 | Página pública, formulario de inscripción, panel de inscritos |
| Tesorería | `C:\laragon\www\tesoreria` | Python 3.10, Django 5.2 LTS, DRF, PostgreSQL 14 (`spp_tesoreria`) | Recibos con numeración correlativa, PDF, anulación, roles, auditoría |

Flujo principal:

1. El asistente llena el formulario en la landing → `POST /api/eventos/congreso-2027/inscripciones` → se guarda como `pendiente` con un código `SPP27-XXXXXX`.
2. Un operador abre el panel de inscritos (CodeIgniter) y pulsa «Registrar pago»: se abre el panel de Tesorería (Django) con los datos precargados por URL.
3. Tesorería emite el recibo (transacción: persona, número, recibo, auditoría y evento de salida) y envía el PDF por correo.
4. Tesorería avisa a Eventos con un **webhook firmado** (`pago.confirmado` / `pago.anulado`) → `POST /api/integracion/pagos`. La inscripción pasa a `pagada` con el folio, o vuelve a `pendiente` si se anula.

Documentos de contexto: `congreso/docs/arquitectura-sistema-spp.md` (arquitectura general), `congreso/docs/congreso-ci4.md` (guía de CodeIgniter), `tesoreria/README.md` (guía de Django), `congreso/docs/demo-next-notas.md` (notas del demo anterior en Next.js, ya eliminado).

## 2. Decisiones de diseño tomadas (no son errores)

- Tesorería es un sistema **aparte** (Django) por decisión del cliente; Eventos no lee sus tablas.
- Persona única por documento en Tesorería; Eventos guarda solo `persona_id` (el id de Tesorería, que llega en el webhook `pago.confirmado`).
- Dinero en **centavos enteros**; nunca decimales flotantes en la base.
- Los recibos **no se editan ni se borran**: solo se anulan con motivo; el número no se reutiliza.
- Avisos entre sistemas por **bandeja de salida (outbox) transaccional** con reintentos.
- Los datos de tarjeta nunca pasan por estos sistemas (no hay pasarela aún; los pagos son manuales: Yappy, transferencia, efectivo, etc.).
- Los precios están en `NULL` («Por confirmar») hasta recibir el tarifario oficial.

## 3. Cómo ejecutarlo

Requisitos: Laragon (Apache en `:8080`, MySQL, PostgreSQL 14), PHP 8.2, Composer, Node, Python 3.10.

**Landing / Eventos (CodeIgniter)**
```bash
cd C:\laragon\www\congreso
composer install
npm install && npm run build:css          # compila public/assets/css/app.css
php spark migrate -n "Modules\Eventos"
php spark migrate -n "Modules\Identidad"
php spark db:seed "Modules\Eventos\Database\Seeds\Congreso2027Seeder"
# URL: http://congreso.test:8080/   (panel: /admin/login)
```

**Tesorería (Django)**
```bash
cd C:\laragon\www\tesoreria
.venv\Scripts\python manage.py migrate
.venv\Scripts\python manage.py crear_roles
.venv\Scripts\python manage.py runserver 8090      # http://localhost:8090/
DB_ENGINE=sqlite .venv\Scripts\python manage.py test apps    # 17 pruebas (también pasan en PostgreSQL)
```

Los secretos están en `.env` de cada proyecto (no se suben a git). **No imprimas ni copies sus valores en tu informe.** `tesoreria.webhookSecret` (CodeIgniter) debe ser igual a `EVENTOS_WEBHOOK_SECRET` (Django).

## 4. Mapa de archivos clave

**CodeIgniter** (`congreso/app/Modules/`, cada módulo es su propio namespace, ver `app/Config/Autoload.php`):

- `Landing/` → `Controllers/Home.php` (página y formulario de contacto), `Views/pagina.php` + `Views/sections/*.php`, `Config/Congreso.php` (textos editables), `Helpers/landing_helper.php`.
- `Eventos/` →
  - `Controllers/InscripcionController.php` (API pública de inscripción: throttling, honeypot, validación)
  - `Controllers/WebhookTesoreriaController.php` (receptor del webhook: HMAC, marca de tiempo, idempotencia)
  - `Controllers/AdminInscripcionController.php` (lista, cancelar, exportar CSV)
  - `Services/EventoService.php` (catálogo, vigencia de tarifas, registrar, pago/anulación)
  - `Services/NotificadorInscripcion.php` (correos), `Database/Migrations/*`
- `Identidad/` → login (`Services/AuthService.php`), filtro `Filters/AuthFilter.php`, `Services/Auditoria.php`.
- Frontend: `public/assets/js/landing.js`, estilos fuente `assets-src/app.css` → `public/assets/css/app.css`.
- Configuración relevante: `app/Config/Filters.php` (CSRF, `secureheaders`, excepción del webhook), `.env`.

**Django** (`tesoreria/`):

- `config/settings.py` (seguridad, axes, 2FA opcional, correo, webhook), `config/sites.py`.
- `apps/tesoreria/` → `models.py`, `servicios.py` (**emitir/anular: lo más crítico**), `admin.py` (panel y vistas), `forms.py`, `pdf.py`, `correo.py`, `tests.py`.
- `apps/integracion/` → `servicios.py` (outbox, firma HMAC, reintentos), `auth.py` (token de servicio), `views.py` (API de consulta).
- `apps/auditoria/` (modelo de solo inserción), `apps/identidad/management/commands/crear_roles.py`.

## 5. Qué se probó y qué no

**Probado**
- 17 pruebas automáticas de Django (numeración correlativa, un recibo vigente por cobro, anulación y permisos, auditoría de solo inserción, firma del webhook, reintentos, API con token, formulario del panel), sobre SQLite **y** PostgreSQL.
- Flujo real entre ambos sistemas: inscripción pública → recibo → inscripción `pagada` con folio → anulación → vuelve a `pendiente`; firma inválida → 401; marca de tiempo vieja → 401; evento repetido → «Ya procesado».
- Landing revisada visualmente en escritorio y celular (375 px), formulario de inscripción de punta a punta, panel de inscritos y panel de Tesorería.

**No probado** (revisar con especial cuidado)
- Envío de correo con **SMTP real** (en desarrollo los correos se guardan como archivos).
- Carga/concurrencia real (la numeración usa `SELECT … FOR UPDATE`; solo hay prueba secuencial).
- Navegadores distintos de Chromium, lectores de pantalla, Safari/iOS.
- Despliegue en producción (HTTPS, Nginx/Apache, HSTS, cookies `Secure`).
- 2FA (`REQUIRE_2FA`) activado; está implementado con `django-otp` pero apagado.

## 6. Qué pedimos que revises

### Seguridad (prioridad alta)
1. **Webhook** (`WebhookTesoreriaController.php` ↔ `apps/integracion/servicios.py`): ¿la firma `HMAC(secreto, marca_tiempo + "." + cuerpo)` está bien verificada? ¿Hay riesgo de *replay* dentro de los 300 s? ¿La idempotencia con `SELECT … FOR UPDATE` evita doble proceso? ¿Se confía en algún dato del cuerpo antes de verificar la firma?
2. **Autenticación del panel CodeIgniter** (`AuthService.php`, `AuthFilter.php`): bloqueo por intentos, regeneración de sesión, redirección posterior al login (`despues_login`), protección CSRF en `logout` y demás POST.
3. **Django**: `settings.py` en modo producción (`DEBUG=false`), `ALLOWED_HOSTS`, cookies, `CSRF_TRUSTED_ORIGINS`, validadores de contraseña, `django-axes`, permisos por grupo (`crear_roles.py`), que un Operador no pueda anular ni leer auditoría.
4. **Inyección y XSS**: vistas PHP (`esc()` en todas las salidas), plantillas Django, exportación CSV (hay mitigación de inyección de fórmulas), parámetros de URL que precargan el formulario de emisión.
5. **Enlace de precarga entre sistemas**: el panel de CodeIgniter construye una URL con datos personales (documento, correo, teléfono) en la **query string** hacia Tesorería. ¿Es aceptable o conviene un POST/token de un solo uso? (queda en logs y en el historial del navegador).
6. **API pública de inscripción** (`InscripcionController.php`): throttling por IP, campo trampa, validación, mensajes que revelan si un documento ya está inscrito (enumeración).
7. **Privacidad de datos** (ley panameña de protección de datos): qué se guarda, cuánto tiempo, consentimiento, cifrado de campos sensibles (hoy **no** hay cifrado a nivel de campo).
8. **Auditoría**: hoy la tabla es de solo inserción solo a nivel de código; en la base el usuario de la app sí podría hacer `UPDATE/DELETE`.

### Corrección y datos
9. **`apps/tesoreria/servicios.py`**: orden de operaciones dentro de la transacción, `select_for_update` sobre `SerieRecibo`, manejo de `IntegrityError`, que un fallo del outbox no deje un recibo sin aviso (o un aviso sin recibo), uso de `transaction.on_commit`.
10. **Consistencia entre sistemas**: ¿qué pasa si el webhook de anulación llega antes que el de confirmación, o si el recibo cobra una inscripción ya cancelada? (`EventoService::registrarPago/revertirPago`).
11. **Dinero y zona horaria**: conversión de monto a centavos (`forms.py`), `fecha_pago` vs. `TIME_ZONE = America/Panama`, vigencia de la preventa (`fin_preventa`, 15-mar-2027 23:59:59, hora de Panamá).
12. **Migraciones**: ¿son reversibles y seguras en producción? (`Eventos/Database/Migrations/*`, `apps/*/migrations`).
13. **Índices y consultas**: listados con filtros `LIKE`, límite de filas en el panel y en la exportación.

### Diseño y mantenibilidad
14. Límites entre módulos: ¿alguna clase accede a datos de otro módulo saltándose su servicio? ¿Qué impediría separar más adelante el módulo de Eventos?
15. Duplicación y código muerto (el demo de Next.js se eliminó; revisa restos: `docs/demo-next-notas.md`, referencias a Next, íconos sobrantes).
16. Accesibilidad y SEO de la landing (`Views/pagina.php`, metaetiquetas, `landing.js`), rendimiento (imágenes, fuentes de Google).
17. Calidad de las pruebas: ¿qué casos faltan? (CodeIgniter **no tiene pruebas automáticas**).

## 7. Pendientes conocidos (no los reportes como hallazgos nuevos)

- SMTP real y programar `manage.py entregar_eventos` (reintento de avisos, cada minuto).
- Activar `REQUIRE_2FA=true` cuando cada usuario registre su dispositivo.
- Usuario de PostgreSQL de producción con contraseña y sin `UPDATE/DELETE` sobre la auditoría. En desarrollo la base usa el usuario `darango` sin contraseña (**solo local**).
- Collation de la base de Tesorería en `C` (ordena apellidos con tildes por bytes).
- `persona_id` en `inscripciones` se toma del webhook sin contrastarlo con el documento; no se verificó explícitamente que quede guardado en las pruebas (sí se verificó el estado y el folio).
- Decisión fiscal con contabilidad: **recibo simple o factura electrónica (DGI)**; hoy solo hay recibo.
- Precios oficiales, enlace de Grand Tours, logos, fotos y textos de ejemplo de la landing (marcados como `PLACEHOLDER`).
- Gestión de usuarios del panel de CodeIgniter (solo existe el administrador sembrado), 2FA allí, estado de cuenta para socios, pasarela de pago en línea, certificados.

## 8. Formato de respuesta esperado

Entrega una lista ordenada por severidad (**Crítico / Alto / Medio / Bajo**). Para cada hallazgo:

- Archivo y línea (`ruta:línea`).
- Qué está mal y **un escenario concreto** donde falla (entrada → resultado incorrecto).
- Cómo corregirlo (idealmente con el cambio propuesto).
- Si no pudiste verificarlo ejecutando el código, indícalo.

Al final, resume qué partes revisaste y cuáles no, y lista las cosas que están bien y no deberían tocarse.

**Reglas para el revisor:** no ejecutes comandos destructivos (no `TRUNCATE`, `DROP`, `migrate:refresh` ni borres `.env`); no publiques ni envíes correos reales; no pegues secretos del `.env` en tu respuesta; los datos de prueba de las bases pueden crearse y borrarse, pero avisa antes de tocar la base.

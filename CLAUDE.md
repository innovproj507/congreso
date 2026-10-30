# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Landing pública y registro de inscritos del LV Congreso Nacional de Pediatría 2027. PHP 8.2 / CodeIgniter 4.7 + MySQL (DB `spp_congreso_2027`; la `spp_congreso` de 2026 no se toca). Los docs y textos están en español. Más detalle en [docs/congreso-ci4.md](docs/congreso-ci4.md) y [docs/arquitectura-sistema-spp.md](docs/arquitectura-sistema-spp.md).

## Commands

```bash
composer install
cp env .env                                   # baseURL, DB, SMTP (email.*), tesoreria.*
php spark migrate -n "Modules\Eventos"        # migraciones por módulo (-n namespace)
php spark migrate -n "Modules\Identidad"
php spark db:seed "Modules\Eventos\Database\Seeds\Congreso2027Seeder"
php spark db:seed "Modules\Identidad\Database\Seeds\AdminSeeder"   # usa admin.correo / admin.password de .env
php spark serve --port 8081
php spark galeria:procesar [--forzar]         # genera WebP/miniaturas de spp/galeria/ en public/img/galeria/
npm run build:css                             # Tailwind v4: assets-src/app.css -> public/assets/css/app.css (versionado)
npm run watch:css
composer test                                 # phpunit (tests/ es casi solo el esqueleto de CI4)
vendor/bin/phpunit --filter NombreTest        # un solo test
```

`public/assets/css/app.css` es salida compilada pero está commiteada: tras editar `assets-src/app.css` o clases Tailwind en las vistas, ejecutar `npm run build:css` y commitear ambos.

## Architecture

Código modular bajo `app/Modules/`, cada módulo con su namespace registrado en `app/Config/Autoload.php` (`Modules\Eventos`, `Modules\Landing`, `Modules\Identidad`) y con sus propias rutas (`Config/Routes.php`), vistas y migraciones:

- **Landing**: páginas públicas. `Views/pagina.php` define el orden de las secciones (`Views/sections/*`); el contenido editable y el `$menu` viven en `Config/Congreso.php` (valores sobreescribibles vía `.env`, p. ej. `congreso.grandToursUrl`, `congreso.indexar`). Varias secciones (sociedad, faq, agenda, premio…) están sin incluir pero se conservan; se reactivan añadiendo su `include` en `pagina.php`. Landing solo habla con Eventos a través de `Modules\Eventos\Services\EventoService`, nunca toca sus tablas.
- **Eventos**: eventos, tarifas con vigencia (`precio_centavos`, NULL = "Por confirmar", 0 = sin costo), inscripciones, correos (`NotificadorInscripcion`), panel `/admin` de inscritos y el webhook de Tesorería. Las tarifas oficiales salen de `Database/TarifasOficiales.php` (si cambian, también hay que cambiar el arte `INSCRIPCIONES.jpg`). Las instrucciones de pago están en la columna `formas_pago.instrucciones`.
- **Identidad**: usuarios internos, login con bloqueo, filtro `auth`, auditoría de solo inserción.
- **Tesorería** es un sistema aparte (Django, `www/tesoreria`). Se integra por webhook firmado `POST /api/integracion/pagos` (HMAC-SHA256 + marca de tiempo + evento único; eventos `pago.confirmado` / `pago.anulado`); el secreto `tesoreria.webhookSecret` debe coincidir en ambos lados. La inscripción se guarda siempre primero en la DB de CI, aunque Tesorería esté caída.

### Imágenes y galería

- `spp/` contiene los originales (no se sirven por web). Se convierten a WebP en `public/img/` y las vistas usan `<picture>`: cada pieza tiene versión escritorio y celular (`_m`, ≤767 px); ver la tabla en docs/congreso-ci4.md.
- La galería («Revive el Congreso 2026») se arma sola desde `spp/galeria/` (`Services/GaleriaService.php`, comando `GaleriaProcesar`, visor en `public/assets/js/landing.js`); en desarrollo se procesa al recargar, en producción hay que correr `galeria:procesar`. Fotos retiradas van a `spp/galeria-eliminadas/`.
- El JS de la landing es un solo archivo vanilla, `public/assets/js/landing.js` (sin bundler).

### Convenciones del formulario de inscripción

Se valida en navegador y en servidor (manda el servidor); nombres se normalizan, documento en mayúsculas, correo en minúsculas. Mismo documento + mismo correo = reenvío de la inscripción existente; mismo documento con otro correo se rechaza. CSRF en todo POST, campo trampa anti-bots y límite de intentos por IP.

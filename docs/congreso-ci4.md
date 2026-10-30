# Landing + inscripciones (CodeIgniter 4)

Landing del LV Congreso Nacional de Pediatría 2027 en PHP/CodeIgniter 4, con registro de participantes en MySQL.
El demo en Next.js (`src/`, `package.json`) se conserva solo como referencia de diseño.

## Estructura modular

```
app/Modules/
  Landing/   páginas públicas: vistas (sections/), contenido editable (Config/Congreso.php), contacto
  Eventos/   eventos, tarifas con vigencia, inscripciones: migraciones, modelos, EventoService, API, correos, panel de inscritos
  Identidad/ usuarios internos (administración, gerencia, operador), login con bloqueo, filtro `auth`, auditoría de solo inserción
```

Tesorería es un sistema aparte (Django, carpeta `www/tesoreria`). Se comunica con Eventos por webhook firmado: Tesorería avisa `pago.confirmado` y `pago.anulado` a `POST /api/integracion/pagos` (HMAC-SHA256, marca de tiempo y evento único). El secreto está en `.env` (`tesoreria.webhookSecret`) y debe ser igual al de Tesorería.
Panel interno: `/admin` (inscritos). El botón «Registrar pago» abre el panel de Tesorería (`tesoreria.panelUrl`) con los datos del inscrito precargados.

- Cada módulo es su propio namespace (`Modules\Eventos`, `Modules\Landing`, ver `app/Config/Autoload.php`) con sus rutas, vistas y migraciones.
- Landing solo usa `Modules\Eventos\Services\EventoService`; no toca las tablas de Eventos.
- Tesorería será otro módulo. Eventos guarda `persona_id` (vacío por ahora) en lugar de datos de personas de Tesorería.

## Puesta en marcha

```bash
composer install
cp env .env                      # ajustar baseURL, base de datos y correo (SMTP)
php spark migrate -n "Modules\Eventos"
php spark migrate -n "Modules\Identidad"
php spark db:seed "Modules\Eventos\Database\Seeds\Congreso2027Seeder"
# primer usuario del panel: definir admin.correo y admin.password en .env y luego
php spark db:seed "Modules\Identidad\Database\Seeds\AdminSeeder"
npm install && npm run build:css  # compila public/assets/css/app.css (Tailwind)
php spark serve --port 8081
```

- Base de datos: `spp_congreso_2027`. La `spp_congreso` (2026) no se toca.
- Precios: tabla `tarifas.precio_centavos` (NULL = "Por confirmar"). Ejemplo: `UPDATE tarifas SET precio_centavos = 15000 WHERE ...` para $150.
- Fin de preventa: `eventos.fin_preventa` (hora de Panamá). Después de esa fecha se muestra y cobra la tarifa regular.
- Inscripciones: `SELECT * FROM inscripciones` (estado `pendiente` hasta confirmar el pago).
- Correo: requiere SMTP real en `.env` (`email.*`). Si falla, la inscripción queda guardada (`correo_enviado_at` vacío) y el error va a `writable/logs`.
- Para publicar sin `noindex`: `congreso.indexar = true` en `.env`.

## Seguridad aplicada

CSRF en todo POST, `secureheaders`, límite de intentos por IP, campo trampa anti-bots, validación en servidor,
salida escapada en las vistas, código único por inscripción, un documento por evento.

## Galería dinámica («Revive el Congreso 2026»)

La galería se arma sola con lo que haya en `spp/galeria/`:

- **Agregar o quitar fotos:** copia o borra imágenes (`jpg`, `jpeg`, `png`, `webp`) en `spp/galeria/`. En desarrollo basta con recargar la página; en producción ejecuta `php spark galeria:procesar` (agrega `--forzar` para regenerar todo).
- **Video:** el primer `mp4`, `webm` o `mov` de la carpeta aparece como tarjeta de video; la portada se saca con ffmpeg (si no está instalado, se usa una foto).
- **Orden:** Revive el Congreso 2026 → Carta de bienvenida → Banner principal → Inscripciones (arte + formulario) → «Tu viaje comienza aquí» (Grand Tours) → franja de aliados → Contacto. Se quitaron los bloques «Nosotros», «Experiencia», «Nuestra celebración», «3 pasos», «Premio / trabajos libres», «Agenda» y «Preguntas frecuentes»; sus vistas siguen en `Views/sections/` (sociedad, experiencia, congreso, pasos, premio, agenda, faq) por si se vuelven a necesitar: basta añadir su `include` en `Views/pagina.php`. El orden se cambia en `Views/pagina.php` y el menú en `Config/Congreso.php`.
- **Textos:** opcional, crea `spp/galeria/titulos.json` con `{"01-apertura.jpg": "Acto de apertura"}`; sin él, las fotos llevan un texto genérico.
- **Qué publica:** versiones WebP de hasta 1600 px y miniaturas de 640 px en `public/img/galeria/` (nunca agranda una foto pequeña). Los originales no se sirven por web.
- **Con 6 fotos o más** hay carrusel continuo en dos filas y mosaico («Ver todas»); con menos, solo el mosaico. Si la carpeta está vacía, la sección no se muestra.

Código: `app/Modules/Landing/Services/GaleriaService.php`, comando `Commands/GaleriaProcesar.php`, vista `Views/sections/galeria.php` y el visor en `public/assets/js/landing.js`.

## Orden de la landing, tarifas y enlace de Grand Tours

- **Orden:** Revive el Congreso 2026 → Carta de bienvenida → Banner principal → Inscripciones (arte + formulario) → «Tu viaje comienza aquí» (Grand Tours) → Nosotros, El Congreso, Premio, Agenda, Preguntas y Contacto. Se cambia en `app/Modules/Landing/Views/pagina.php` y el menú en `Config/Congreso.php`.
- **Arte oficial:** `spp/CARTA BIENVENIDA.jpg` y `spp/INSCRIPCIONES.jpg` (versiones WebP en `public/img/`). En pantallas medianas y grandes se muestra el arte; en celular, donde el texto sería ilegible, se muestra el mismo contenido como texto (la carta, y la tabla de tarifas leída de la base de datos).
- **Tarifas oficiales** (Early Bird 30-oct-2026 a 15-mar-2027 y Regular desde el 16-mar-2027): `app/Modules/Eventos/Database/TarifasOficiales.php`. Si cambian, edita esa lista y vuelve a sembrar, o actualiza la tabla `tarifas` (precios en centavos; 0 = sin costo). Si cambias precios, **cambia también el arte** `INSCRIPCIONES.jpg`.
- **Enlace de Grand Tours:** `https://dmc.grandtours.com.pa/congreso-pediatria` (valor por defecto en `Config/Congreso.php`; se cambia en `.env` con `congreso.grandToursUrl = '...'`; si queda vacío, el botón dice «próximamente»). Debajo del botón va el aviso «Aplica para visitantes del extranjero.».
- **Logos de aliados:** originales en `spp/grandtours.png` y `spp/baby-castille.jpeg`; versiones WebP recortadas en `public/img/logo-grandtours.webp` y `public/img/logo-babycastille.webp`. Se usan en «Participantes internacionales» (junto al de la Sociedad), en el Premio (Baby Castille), en la franja de aliados y en el pie de página.

## Formulario de inscripción: cómo funciona

- **Dónde se guarda:** la inscripción se guarda en **la base de datos de CodeIgniter** (`inscripciones`, módulo Eventos). Es lo que hace que el formulario funcione siempre, aunque Tesorería esté caída. Tesorería (Django) se entera después, por webhook firmado, cuando se emite el recibo.
- **Validación:** en el navegador (mensaje junto a cada campo, con foco en el primer error) y otra vez en el servidor, que es la que manda. Nombres en mayúsculas o minúsculas se normalizan («JUAN PÉREZ» → «Juan Pérez»), el documento va en mayúsculas y el correo en minúsculas.
- **Reenvío seguro:** si la misma persona (mismo documento **y** mismo correo) vuelve a enviar el formulario, se le devuelve su inscripción y se le reenvían las indicaciones; no se crea otra. Con el mismo documento y otro correo se rechaza.
- **Instrucciones de pago:** viven en la columna `formas_pago.instrucciones` (`{correo}` se reemplaza por el correo de contacto). Se muestran al elegir el medio de pago, en la pantalla de confirmación y en el correo. Se editan en la tabla, sin tocar código; revisa los textos de Visa, Mastercard y Clave, que hoy piden escribir al correo para coordinar.

## Encabezado sin menú (página de desplazamiento)

La landing es de **desplazamiento continuo, sin menú de navegación** (como el sitio de referencia del cliente): arriba solo va un encabezado con el logo de la Sociedad y el nombre del congreso con fecha y sede (en celular apilados y centrados; en escritorio lado a lado, con una línea divisoria). Vive en `Views/sections/navbar.php`. Al bajar aparece un botón flotante «Inscríbete» que lleva al formulario y se oculta cuando ya se ven las inscripciones.

Si el cliente quiere menú de nuevo, el listado de secciones sigue en `Config/Congreso.php` (`$menu`, que también alimenta los «Enlaces rápidos» del pie) y bastaría reponer la barra en `navbar.php`.

## Imágenes de la carpeta `spp/`: versión de escritorio y de celular

Cada pieza tiene dos archivos en `spp/`: el de escritorio y el de celular (terminado en `_m`). Los originales no se publican: se convierten a WebP en `public/img/` (script de apoyo `imagenes_pc_movil.py` descrito abajo) y la página elige uno u otro con `<picture>` (celular = pantallas de hasta 767 px).

| Pieza | Escritorio | Celular | Salidas en `public/img/` |
|---|---|---|---|
| Banner principal | `banner_p.jpg` | `banner_p_m.jpg` | `banner-principal.webp` · `banner-movil.webp` |
| Carta de bienvenida | `carta.jpg` | `carta_m.jpg` | `carta-bienvenida.webp` · `carta-bienvenida-movil.webp` |
| Inscripciones | `incripciones.jpg` | `incripciones_m.jpg` | `inscripciones.webp` · `inscripciones-movil.webp` |
| Grand Tours | `grand_tours.jpg` | `grand_tours_m.jpg` | `grandtours-banner.webp` · `grandtours-banner-movil.webp` |

Para cambiar una imagen: reemplaza el archivo en `spp/` (mismo nombre) y vuelve a generar el WebP (escritorio 2000–2400 px de ancho, celular 1000 px, calidad 84). La carta y las tarifas también existen como texto (botón «Leer la carta como texto» / «Ver tarifas como texto» en celular, y para lectores de pantalla en escritorio). Si cambias precios, actualiza también `TarifasOficiales.php`.


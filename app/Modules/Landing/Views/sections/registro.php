<?php
/** @var array|null $catalogo */
$formas = $catalogo['formas'] ?? [];
$abierto = (int) ($catalogo['evento']['inscripciones_abiertas'] ?? 0) === 1;
$campo = 'w-full rounded-2xl border border-esm-100 bg-white px-5 py-3.5 text-base outline-none transition focus:border-esm-700 focus:ring-2 focus:ring-esm-300/40';
$label = 'mb-2 block text-sm font-semibold text-esm-900';
$paises = ['Panamá', 'Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Costa Rica', 'Cuba', 'Ecuador', 'El Salvador', 'España', 'Estados Unidos', 'Guatemala', 'Honduras', 'México', 'Nicaragua', 'Paraguay', 'Perú', 'Puerto Rico', 'República Dominicana', 'Uruguay', 'Venezuela'];
?>
<?= seccion_inicio('registro', 'Reserva tu cupo', 'Formulario de inscripción') ?>
<div class="reveal mx-auto max-w-4xl" id="registro-caja">
    <?php if (! $catalogo || ! $abierto) : ?>
        <div class="rounded-[30px] bg-esm-50 p-8 text-center sm:p-12">
            <h3 class="font-display text-[26px] font-medium text-esm-900">Las inscripciones abrirán pronto</h3>
            <p class="mt-3 text-ink/75">Escríbenos a <a class="font-semibold text-esm-700 underline" href="mailto:<?= esc($c->correo, 'attr') ?>"><?= esc($c->correo) ?></a> y te avisamos.</p>
        </div>
    <?php else : ?>
        <?php /* Pantalla de confirmación: resumen, código copiable e instrucciones del medio de pago elegido */ ?>
        <div id="registro-ok" class="hidden rounded-[30px] bg-esm-50 p-6 sm:p-10" role="status" aria-live="polite" tabindex="-1">
            <div class="text-center">
                <?= icono('circle-check', 56, 'mx-auto text-esm-800') ?>
                <h3 id="ok-titulo" class="font-display mt-4 text-[26px] font-medium text-esm-900 sm:text-[30px]">¡Recibimos tu solicitud!</h3>
                <p id="ok-saludo" class="mt-2 text-ink/75"></p>
            </div>
            <div class="mx-auto mt-6 max-w-xl rounded-[24px] bg-white p-5 shadow-sm ring-1 ring-esm-100">
                <p class="text-xs font-bold uppercase tracking-widest text-esm-700">Tu código de inscripción</p>
                <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
                    <strong id="ok-codigo" class="font-display text-3xl tracking-wider text-esm-900"></strong>
                    <button type="button" id="ok-copiar" class="rounded-full border border-esm-800 px-4 py-2 text-sm font-semibold text-esm-800 transition hover:bg-esm-50">Copiar código</button>
                </div>
                <dl class="mt-4 divide-y divide-esm-100 text-sm">
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink/60">Categoría</dt><dd id="ok-categoria" class="text-right font-medium"></dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink/60">Monto</dt><dd id="ok-precio" class="text-right font-bold text-esm-800"></dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink/60">Forma de pago</dt><dd id="ok-pago" class="text-right font-medium"></dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt class="text-ink/60">Correo</dt><dd id="ok-correo" class="break-all text-right font-medium"></dd></div>
                </dl>
            </div>
            <div class="mx-auto mt-5 max-w-xl rounded-[24px] border border-esm-300 bg-white/70 p-5 text-sm">
                <p class="font-semibold text-esm-900">Siguiente paso: tu pago</p>
                <p id="ok-instrucciones" class="mt-1 leading-6 text-ink/80"></p>
                <p class="mt-3 text-ink/70">Con tu comprobante emitiremos tu recibo y confirmaremos tu inscripción. También te enviamos estas indicaciones por correo (revisa la carpeta de spam).</p>
            </div>
            <p class="mt-6 text-center"><button type="button" id="ok-otra" class="text-sm font-semibold text-esm-700 underline">Inscribir a otra persona</button></p>
        </div>

        <form id="registro-form" novalidate class="rounded-[30px] border border-esm-100 bg-white p-6 shadow-sm sm:p-10">
            <div class="mb-8 rounded-[30px] bg-esm-900 px-6 py-5 text-center text-white">
                <p class="text-sm">Tarifa vigente: <strong id="tarifa-titulo" class="font-display text-base"></strong>
                    <span id="tarifa-rango" class="block text-esm-300"></span></p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="<?= $label ?>" for="categoria">Categoría de inscripción *</label>
                    <select id="categoria" name="categoria" class="<?= $campo ?>"></select>
                </div>
                <div><label class="<?= $label ?>" for="nombre">Nombre *</label>
                    <input id="nombre" name="nombre" required maxlength="100" autocomplete="given-name" autocapitalize="words" aria-describedby="err-nombre" class="<?= $campo ?>">
                    <p class="campo-msg" id="err-nombre" hidden></p></div>
                <div><label class="<?= $label ?>" for="apellido">Apellido *</label>
                    <input id="apellido" name="apellido" required maxlength="100" autocomplete="family-name" autocapitalize="words" aria-describedby="err-apellido" class="<?= $campo ?>">
                    <p class="campo-msg" id="err-apellido" hidden></p></div>
                <div><label class="<?= $label ?>" for="documento">Cédula o pasaporte *</label>
                    <input id="documento" name="documento" required maxlength="40" autocapitalize="characters" spellcheck="false" aria-describedby="ayuda-documento err-documento" class="<?= $campo ?>">
                    <p id="ayuda-documento" class="mt-1 text-xs text-ink/60">Ej.: 8-123-456. Escríbela como aparece en tu documento.</p>
                    <p class="campo-msg" id="err-documento" hidden></p></div>
                <div><label class="<?= $label ?>" for="pais">País de residencia *</label>
                    <input id="pais" name="pais" required maxlength="80" value="Panamá" list="lista-paises" autocomplete="country-name" aria-describedby="err-pais" class="<?= $campo ?>">
                    <datalist id="lista-paises"><?php foreach ($paises as $p) : ?><option value="<?= esc($p, 'attr') ?>"></option><?php endforeach; ?></datalist>
                    <p class="campo-msg" id="err-pais" hidden></p></div>
                <div><label class="<?= $label ?>" for="correo">Correo electrónico *</label>
                    <input id="correo" name="correo" type="email" required maxlength="150" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" aria-describedby="ayuda-correo err-correo" class="<?= $campo ?>">
                    <p id="ayuda-correo" class="mt-1 text-xs text-ink/60">Aquí te enviaremos tu código y tu recibo.</p>
                    <p class="campo-msg" id="err-correo" hidden></p>
                    <button type="button" id="sugerencia-correo" class="mt-1 hidden text-sm font-semibold text-esm-700 underline"></button></div>
                <div><label class="<?= $label ?>" for="telefono">Teléfono / WhatsApp *</label>
                    <input id="telefono" name="telefono" type="tel" required maxlength="40" autocomplete="tel" inputmode="tel" aria-describedby="ayuda-telefono err-telefono" class="<?= $campo ?>">
                    <p id="ayuda-telefono" class="mt-1 text-xs text-ink/60">Con código de país si vives fuera de Panamá (ej. +57 300 123 4567).</p>
                    <p class="campo-msg" id="err-telefono" hidden></p></div>
                <div class="sm:col-span-2"><label class="<?= $label ?>" for="institucion">Institución o centro de trabajo <span class="font-normal text-ink/60">(opcional)</span></label>
                    <input id="institucion" name="institucion" maxlength="200" autocomplete="organization" class="<?= $campo ?>"></div>
            </div>

            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                <label>No llenar este campo <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
            </div>

            <div id="aviso-extranjero" class="mt-6 hidden rounded-[30px] border border-esm-300 bg-esm-50 p-5 text-sm">
                <p class="font-semibold text-esm-900">¿Vienes del extranjero?</p>
                <p class="mt-1 text-ink/75">Con Grand Tours puedes reservar paquetes de hotel, tiquetes y traslados que incluyen la inscripción. Aplica para visitantes del extranjero.</p>
                <?php if ($c->grandToursUrl !== '') : ?>
                    <a href="<?= esc($c->grandToursUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-2 font-semibold text-esm-700 underline">
                        Ver paquetes con Grand Tours <?= icono('external-link', 16) ?></a>
                <?php else : ?>
                    <a href="#internacionales" class="mt-3 inline-flex items-center gap-2 font-semibold text-esm-700 underline">Ver información para visitantes internacionales</a>
                <?php endif; ?>
            </div>

            <fieldset class="mt-8">
                <legend class="<?= $label ?>">¿Cómo vas a pagar? *</legend>
                <div class="flex flex-wrap gap-3">
                    <?php foreach ($formas as $i => $f) : ?>
                        <label class="pago-pill cursor-pointer rounded-full border border-esm-100 bg-white px-5 py-2.5 text-sm font-semibold text-esm-900 transition hover:border-esm-700 focus-within:ring-2 focus-within:ring-esm-300">
                            <input type="radio" name="pago" value="<?= esc($f, 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?> class="sr-only"><?= esc($f) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p id="pago-instrucciones" class="mt-3 hidden rounded-2xl bg-esm-50 px-4 py-3 text-sm leading-6 text-ink/80" aria-live="polite"></p>
            </fieldset>

            <label class="mt-8 flex items-start gap-3 text-sm text-ink/75">
                <input type="checkbox" name="acepta" required aria-describedby="err-acepta" class="mt-1 h-4 w-4 accent-[#0e6b5c]">
                Confirmo que mis datos son correctos y autorizo a la Sociedad Panameña de Pediatría a contactarme sobre mi inscripción.
            </label>
            <p class="campo-msg" id="err-acepta" hidden></p>

            <p id="registro-error" role="alert" class="mt-5 hidden rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700"></p>

            <button id="registro-btn" type="submit" class="mt-8 inline-flex w-full items-center justify-center gap-2 rounded-full bg-esm-800 px-[30px] py-[18px] font-semibold text-white transition hover:bg-esm-700 disabled:opacity-60 sm:w-auto">
                <span id="registro-spin" class="hidden animate-spin"><?= icono('loader-circle', 18) ?></span>
                <span id="registro-btn-texto">Enviar inscripción</span>
            </button>
            <noscript><p class="mt-4 text-sm text-red-700">Activa JavaScript para enviar el formulario, o escríbenos a <?= esc($c->correo) ?>.</p></noscript>
        </form>
    <?php endif; ?>
</div>
<?= seccion_fin() ?>

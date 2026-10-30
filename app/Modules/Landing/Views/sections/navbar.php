<?php /* Encabezado de la página (sin menú: la página se recorre hacia abajo).
 * Logo de la Sociedad + nombre del congreso. Celular: apilados y centrados; escritorio: lado a lado con una línea divisoria. */ ?>
<header id="encabezado" class="border-b border-esm-100 bg-white">
    <div class="mx-auto flex max-w-[1320px] flex-col items-center gap-3 px-5 py-5 text-center lg:flex-row lg:justify-center lg:gap-8 lg:py-6 lg:text-left">
        <a href="<?= base_url() ?>" class="shrink-0" aria-label="Sociedad Panameña de Pediatría, ir al inicio">
            <img src="<?= base_url('img/logo.webp') ?>" width="394" height="78" decoding="async" alt="Sociedad Panameña de Pediatría" class="h-14 w-auto sm:h-16 lg:h-20">
        </a>

        <div class="lg:border-l-2 lg:border-esm-100 lg:pl-8">
            <p class="font-display text-xl font-bold leading-tight text-esm-900 sm:text-2xl lg:text-3xl xl:text-4xl"><?= esc($c->nombre) ?></p>
            <p class="font-display mt-1 text-base font-bold uppercase tracking-wide text-esm-700 sm:text-lg lg:text-xl xl:text-2xl"><?= esc($c->edicion) ?> <span class="text-esm-900">55°</span></p>
            <p class="mt-1 text-sm text-ink/70 sm:text-base lg:text-lg"><?= esc($c->fechaTexto) ?> · <?= esc($c->sede) ?></p>
        </div>
    </div>
</header>

<?php /* Botón que acompaña al visitante al bajar; se oculta cuando ya está viendo las inscripciones */ ?>
<a id="btn-flotante" href="#registro" class="fixed bottom-5 right-5 z-40 hidden items-center gap-2 rounded-full bg-esm-800 px-6 py-3.5 font-semibold text-white shadow-xl ring-4 ring-white/70 transition hover:bg-esm-700 sm:bottom-6 sm:right-6">
    Inscríbete <?= icono('arrow-up-right', 18) ?>
</a>

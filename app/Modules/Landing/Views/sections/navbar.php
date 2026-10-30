<?php /* Encabezado de la página (sin menú: la página se recorre hacia abajo).
 * Izquierda: logo de la Sociedad · Centro: nombre del congreso · Derecha: logo de Baby Castille. */ ?>
<header id="encabezado" class="border-b border-esm-100 bg-white">
    <div class="mx-auto flex max-w-[1320px] flex-wrap items-center justify-between gap-x-6 gap-y-3 px-5 py-4 lg:py-5">
        <a href="<?= base_url() ?>" class="shrink-0" aria-label="Sociedad Panameña de Pediatría, ir al inicio">
            <img src="<?= base_url('img/logo.webp') ?>" width="394" height="78" decoding="async" alt="Sociedad Panameña de Pediatría" class="h-10 w-auto sm:h-12 lg:h-14">
        </a>

        <div class="order-last w-full text-center sm:order-none sm:w-auto sm:flex-1">
            <p class="font-display text-base font-bold leading-tight text-esm-900 sm:text-lg lg:text-2xl"><?= esc($c->nombre) ?></p>
            <p class="font-display text-sm font-bold uppercase tracking-wide text-esm-700 sm:text-base lg:text-lg"><?= esc($c->edicion) ?> <span class="text-esm-900">55°</span></p>
            <p class="mt-0.5 text-xs text-ink/70 sm:text-sm"><?= esc($c->fechaTexto) ?> · <?= esc($c->sede) ?></p>
        </div>

        <span class="shrink-0">
            <img src="<?= base_url('img/logo-babycastille.webp') ?>" width="326" height="291" decoding="async" alt="Baby Castille" class="h-12 w-auto sm:h-14 lg:h-16">
        </span>
    </div>
</header>

<?php /* Botón que acompaña al visitante al bajar; se oculta cuando ya está viendo las inscripciones */ ?>
<a id="btn-flotante" href="#registro" class="fixed bottom-5 right-5 z-40 hidden items-center gap-2 rounded-full bg-esm-800 px-6 py-3.5 font-semibold text-white shadow-xl ring-4 ring-white/70 transition hover:bg-esm-700 sm:bottom-6 sm:right-6">
    Inscríbete <?= icono('arrow-up-right', 18) ?>
</a>

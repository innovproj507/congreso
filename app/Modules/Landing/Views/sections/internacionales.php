<?php
/**
 * Participantes internacionales / Grand Tours.
 * Escritorio: spp/grand_tours.jpg · Celular: spp/grand_tours_m.jpg (versión vertical).
 * El botón verde («Clic aquí para más info») abre un panel con dos opciones: reserva de estadía (Grand Tours) y reserva de inscripciones (formulario).
 */
$url = $c->grandToursUrl;
$altBanner = '¡Tu viaje al Congreso de Pediatría 2027 comienza aquí! Nuestros aliados estratégicos de Grand Tours están a cargo de la gestión de reservas de hoteles, tiquetes aéreos y traslados, de acuerdo con las necesidades de cada participante. Clic en el botón para elegir entre reserva de estadía o reserva de inscripciones.';
$banner = '<picture><source media="(max-width: 767px)" srcset="' . base_url('img/grandtours-banner-movil.webp') . '" width="786" height="936">'
    . '<img src="' . base_url('img/grandtours-banner.webp') . '" width="1956" height="736" loading="lazy" decoding="async" alt="' . esc($altBanner, 'attr') . '" class="block h-auto w-full"></picture>';
?>
<section id="internacionales" class="bg-white py-12 lg:py-20" aria-labelledby="inter-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <h2 id="inter-titulo" class="sr-only">Participantes internacionales</h2>

        <?php /* El botón verde del arte es una zona clicable encima de la imagen (posición en % para que escale con ella): abre el panel con las dos opciones. */ ?>
        <div class="reveal relative mx-auto max-w-[560px] overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100 md:max-w-none">
            <?= $banner ?>
            <button type="button" id="inter-abrir" aria-haspopup="dialog" aria-label="Reservar: elegir entre reserva de estadía o reserva de inscripciones"
                class="absolute left-[21.1%] top-[83.9%] h-[8%] w-[57.9%] rounded-full transition hover:bg-white/10 md:left-[15.9%] md:top-[70.1%] md:h-[12.9%] md:w-[29.4%]"></button>
        </div>

        <dialog id="inter-opciones" aria-labelledby="inter-opciones-titulo" class="m-auto w-[calc(100%-2.5rem)] max-w-md rounded-[30px] bg-white p-0 text-ink shadow-2xl backdrop:bg-esm-900/60">
            <div class="p-7">
            <div class="flex items-start justify-between gap-4">
                <h3 id="inter-opciones-titulo" class="font-display text-xl font-bold text-esm-900">¿Qué quieres reservar?</h3>
                <button type="button" data-cerrar aria-label="Cerrar" class="rounded-full p-1 text-ink/60 hover:bg-esm-50 hover:text-esm-900"><?= icono('x', 22) ?></button>
            </div>
            <div class="mt-5 grid gap-3">
                <?php if ($url !== '') : ?>
                    <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 rounded-2xl bg-esm-800 px-5 py-4 font-semibold text-white hover:bg-esm-700">
                        <?= icono('map-pin', 22, 'shrink-0') ?>
                        <span>Reserva de estadía<span class="block text-sm font-normal text-white/80">Paquetes en Grand Tours (se abre en una pestaña nueva)</span></span>
                    </a>
                <?php endif; ?>
                <a href="#registro" data-cerrar class="flex items-center gap-4 rounded-2xl bg-esm-800 px-5 py-4 font-semibold text-white hover:bg-esm-700">
                    <?= icono('list-checks', 22, 'shrink-0') ?>
                    <span>Reserva de inscripciones<span class="block text-sm font-normal text-white/80">Elige tu categoría en el formulario</span></span>
                </a>
            </div>
            </div>
        </dialog>

        <p class="mx-auto mt-5 flex w-fit items-center gap-2 rounded-full bg-esm-50 px-5 py-2 text-sm font-semibold text-esm-800 ring-1 ring-esm-100">
            <?= icono('plane', 16) ?> Aplica para visitantes del extranjero.</p>
    </div>
</section>

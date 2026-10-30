<?php
/**
 * Participantes internacionales / Grand Tours.
 * Escritorio: spp/grand_tours.jpg · Celular: spp/grand_tours_m.jpg (versión vertical).
 * Toda la imagen es el enlace de reservas («Clic aquí para más info»).
 */
$url = $c->grandToursUrl;
$altBanner = '¡Tu viaje al Congreso de Pediatría 2027 comienza aquí! Nuestros aliados estratégicos de Grand Tours están a cargo de la gestión de reservas de hoteles, tiquetes aéreos y traslados, de acuerdo con las necesidades de cada participante. Contamos con paquetes que incluyen la inscripción. Clic aquí para más info.';
$banner = '<picture><source media="(max-width: 767px)" srcset="' . base_url('img/grandtours-banner-movil.webp') . '" width="1000" height="1200">'
    . '<img src="' . base_url('img/grandtours-banner.webp') . '" width="2400" height="875" loading="lazy" decoding="async" alt="' . esc($altBanner, 'attr') . '" class="block h-auto w-full"></picture>';
?>
<section id="internacionales" class="bg-white py-12 lg:py-20" aria-labelledby="inter-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <h2 id="inter-titulo" class="sr-only">Participantes internacionales</h2>

        <div class="reveal mx-auto max-w-[560px] overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100 md:max-w-none">
            <?php if ($url !== '') : ?>
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" class="block transition hover:brightness-105" aria-label="Reservar con Grand Tours: clic aquí para más información (se abre en una pestaña nueva)"><?= $banner ?></a>
            <?php else : ?>
                <?= $banner ?>
            <?php endif; ?>
        </div>

        <p class="mx-auto mt-5 flex w-fit items-center gap-2 rounded-full bg-esm-50 px-5 py-2 text-sm font-semibold text-esm-800 ring-1 ring-esm-100">
            <?= icono('plane', 16) ?> Aplica para visitantes del extranjero.</p>
    </div>
</section>

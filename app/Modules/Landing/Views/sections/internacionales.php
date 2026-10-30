<?php
/**
 * Participantes internacionales / Grand Tours.
 * Escritorio: banner oficial (spp/grandtours.jpg) y todo él es el enlace de reservas («Clic aquí para más info»).
 * Celular: el texto del banner sería ilegible, así que se muestra el mismo contenido como texto con su botón.
 */
$url = $c->grandToursUrl;
$altBanner = '¡Tu viaje al Congreso de Pediatría 2027 comienza aquí! Nuestros aliados estratégicos de Grand Tours están a cargo de la gestión de reservas de hoteles, tiquetes aéreos y traslados, de acuerdo con las necesidades de cada participante. Contamos con paquetes que incluyen la inscripción. Clic aquí para más info.';
$banner = '<img src="' . base_url('img/grandtours-banner.webp') . '" width="2400" height="875" loading="lazy" decoding="async" alt="' . esc($altBanner, 'attr') . '" class="block h-auto w-full">';
?>
<section id="internacionales" class="bg-white py-12 lg:py-20" aria-labelledby="inter-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <h2 id="inter-titulo" class="font-display mb-8 text-center text-[30px] font-bold leading-tight text-esm-900 md:sr-only">Participantes internacionales</h2>

        <?php /* Escritorio: el banner completo es el enlace */ ?>
        <div class="reveal hidden overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100 md:block">
            <?php if ($url !== '') : ?>
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" class="block transition hover:brightness-105" aria-label="Reservar con Grand Tours: clic aquí para más información (se abre en una pestaña nueva)"><?= $banner ?></a>
            <?php else : ?>
                <?= $banner ?>
            <?php endif; ?>
        </div>

        <?php /* Celular: texto y botón */ ?>
        <div class="reveal mx-auto max-w-3xl text-center md:hidden">
            <?= icono('plane', 44, 'mx-auto text-esm-700') ?>
            <p class="font-display mt-4 text-2xl font-bold text-esm-800 sm:text-3xl">¡Tu viaje al Congreso de Pediatría 2027 comienza aquí!</p>
            <p class="mt-5 text-lg leading-relaxed text-ink/75">Nuestros aliados estratégicos de Grand Tours están a cargo de la gestión de reservas de hoteles, tiquetes aéreos y traslados, de acuerdo con las necesidades de cada participante. Contamos con paquetes que incluyen la inscripción. Accede al enlace y verás las opciones disponibles para ti.</p>
            <?php if ($url !== '') : ?>
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer"
                    class="mt-8 inline-flex items-center gap-2 rounded-full bg-esm-800 px-[30px] py-[18px] font-semibold text-white transition hover:bg-esm-700">
                    Reservar con Grand Tours <?= icono('external-link', 18) ?></a>
            <?php else : ?>
                <span class="mt-8 inline-flex cursor-not-allowed items-center gap-2 rounded-full bg-esm-100 px-[30px] py-[18px] font-semibold text-esm-800/70" aria-disabled="true">
                    Reservas con Grand Tours: próximamente</span>
            <?php endif; ?>

            <?php /* Logos de la alianza. El de Grand Tours lleva al sitio de reservas. */ ?>
            <div class="mx-auto mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4" aria-label="Alianza entre la Sociedad Panameña de Pediatría y Grand Tours">
                <img src="<?= base_url('img/logo.webp') ?>" width="394" height="78" loading="lazy" decoding="async" alt="Sociedad Panameña de Pediatría" class="h-12 w-auto">
                <span class="text-2xl font-light text-esm-300" aria-hidden="true">×</span>
                <?php if ($url !== '') : ?><a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" aria-label="Grand Tours (se abre en una pestaña nueva)"><?php endif; ?>
                    <img src="<?= base_url('img/logo-grandtours.webp') ?>" width="720" height="163" loading="lazy" decoding="async" alt="Grand Tours, el gran viaje está por comenzar" class="h-12 w-auto">
                <?php if ($url !== '') : ?></a><?php endif; ?>
            </div>
        </div>

        <p class="mx-auto mt-5 flex w-fit items-center gap-2 rounded-full bg-esm-50 px-5 py-2 text-sm font-semibold text-esm-800 ring-1 ring-esm-100">
            <?= icono('plane', 16) ?> Aplica para visitantes del extranjero.</p>
    </div>
</section>

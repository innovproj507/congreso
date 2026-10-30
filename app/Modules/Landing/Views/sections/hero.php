<?php /* Banner principal: arte oficial (spp/banner_p.jpg). En móvil se muestra el recorte con el título y los aliados van debajo. */ ?>
<section id="inicio" class="mx-3 my-12 lg:mx-5 lg:my-16" aria-label="LV Congreso Nacional de Pediatría, Edición Esmeralda">
    <div class="overflow-hidden rounded-[30px] bg-white shadow-sm ring-1 ring-esm-100">
        <h1 class="sr-only"><?= esc($c->nombre) ?> · <?= esc($c->edicion) ?> 55° · <?= esc($c->lema) ?></h1>
        <picture>
            <source media="(max-width: 767px)" srcset="<?= base_url('img/banner-movil.webp') ?>" width="900" height="546">
            <img src="<?= base_url('img/banner-principal.webp') ?>" width="2400" height="875" fetchpriority="high" decoding="async"
                alt="LV Congreso Nacional de Pediatría, Edición Esmeralda 55°. Dra. Ivonne Abadía de Regalado. Protegiendo el futuro con ciencia sin fronteras. Aliados estratégicos: Baby Castille, SENACYT, Fundación Despega Copa Airlines y Vive Emergencias y Traslados."
                class="block h-auto w-full">
        </picture>

        <div class="grid items-center gap-6 border-t border-esm-100 bg-white px-5 py-6 text-esm-900 lg:grid-cols-[1fr_auto_auto] lg:px-10">
            <div class="flex flex-wrap justify-center gap-x-8 gap-y-2 font-semibold lg:justify-start">
                <span class="flex items-center gap-2"><?= icono('calendar-days', 24, 'text-esm-800') ?> <?= esc($c->fechaTexto) ?></span>
                <span class="flex items-center gap-2"><?= icono('map-pin', 24, 'text-esm-800') ?> <?= esc($c->sede) ?></span>
            </div>

            <div id="cuenta" class="mx-auto grid w-full max-w-sm grid-cols-4 gap-2 lg:mx-0" role="timer" aria-label="Cuenta regresiva para el congreso">
                <?php foreach (['dias' => 'Días', 'horas' => 'Horas', 'min' => 'Min', 'seg' => 'Seg'] as $k => $l) : ?>
                    <div class="rounded-2xl bg-esm-900 py-2 text-center text-white shadow">
                        <div data-cuenta="<?= $k ?>" class="font-display text-2xl font-bold tabular-nums">--</div>
                        <div class="text-[10px] uppercase tracking-wider text-esm-300"><?= $l ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-wrap justify-center gap-3 lg:justify-end">
                <a href="#registro" class="rounded-full bg-esm-800 px-[30px] py-[16px] font-semibold text-white transition hover:bg-esm-700">Inscríbete</a>
                <a href="#internacionales" class="rounded-full border-2 border-esm-900 px-[24px] py-[14px] font-semibold transition hover:bg-esm-900 hover:text-white">Internacionales</a>
            </div>
        </div>
    </div>

    <?php /* Solo móvil: el recorte del banner no incluye los logos, se muestran aquí */ ?>
    <div class="mt-4 overflow-hidden rounded-[30px] bg-white ring-1 ring-esm-100 md:hidden">
        <img src="<?= base_url('img/banner-aliados.webp') ?>" width="1000" height="875" loading="lazy" decoding="async"
            alt="Aliados estratégicos: Baby Castille, SENACYT, Fundación Despega Copa Airlines, Vive. Apoyan: Opella, OM Pharma, Raven, Exeltis, Nicklaus Children's Global Health, Respirator, Mivo, Lucera, NeilMed, Cevaxin, Newport, Histagrip, Altos del María, ABC Pediatrics, FOPA, Designers Pop Up Shop y ELS."
            class="block h-auto w-full">
    </div>
</section>

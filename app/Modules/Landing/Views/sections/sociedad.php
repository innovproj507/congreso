<?php
$tabs = [
    'Misión' => [
        'texto' => 'La Sociedad Panameña de Pediatría agrupa a los pediatras del país y promueve la actualización científica continua para proteger la salud de la niñez.',
        'items' => ['Educación médica continua', 'Investigación y excelencia académica', 'Defensa de la salud infantil', 'Encuentro entre colegas'],
    ],
    'Visión' => [
        'texto' => 'Ser la voz de referencia en salud infantil en Panamá y la región, conectando el conocimiento científico con el cuidado de cada niño.',
        'items' => ['Ciencia sin fronteras', 'Alianzas internacionales', 'Innovación al servicio de la niñez', 'Pediatría basada en evidencia'],
    ],
    'Historia' => [
        'texto' => 'Cumplimos 55 años de trabajo ininterrumpido. El LV Congreso Nacional de Pediatría celebra esta trayectoria con la Edición Esmeralda.',
        'items' => ['55 años de trayectoria', 'LV Congreso Nacional', 'Edición Esmeralda 2027', 'Pro infantis salute'],
    ],
];
$i = 0;
?>
<section id="sociedad" class="bg-white py-20 lg:py-[120px]">
    <div class="mx-auto grid max-w-[1320px] items-center gap-12 px-5 lg:grid-cols-2">
        <div class="reveal">
            <div class="relative mx-auto max-w-md"><?php /* PLACEHOLDER: fotos de la sociedad / congreso */ ?>
                <div class="bg-placeholder aspect-[4/5] rounded-[30px]"></div>
                <div class="absolute -bottom-6 -right-4 rounded-[30px] bg-esm-900 p-6 text-center text-white shadow-xl">
                    <p class="font-display text-5xl font-bold text-gold">55</p>
                    <p class="text-xs uppercase tracking-widest">años</p>
                </div>
            </div>
        </div>
        <div class="reveal" data-tabs>
            <p class="font-display text-base font-bold uppercase leading-none text-esm-700 sm:text-xl">Sociedad Panameña de Pediatría</p>
            <h2 class="font-display mt-3 text-[26px] font-medium leading-[1.4] text-esm-900 sm:text-[40px] sm:leading-[55px] xl:text-[52px] xl:leading-[1.25]">Construyendo el futuro de la niñez con ciencia</h2>
            <div class="mt-8 flex max-w-[620px] justify-between rounded-full border border-esm-100 bg-esm-50 p-2" role="tablist">
                <?php foreach ($tabs as $k => $t) : ?>
                    <button type="button" role="tab" data-tab="<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        class="tab-pill rounded-full px-4 py-[15px] text-xs font-bold uppercase leading-none transition sm:px-[45px] sm:text-base <?= $i === 0 ? 'is-on' : '' ?>"><?= esc($k) ?></button>
                    <?php $i++; endforeach; ?>
            </div>
            <?php $i = 0; foreach ($tabs as $k => $t) : ?>
                <div data-panel="<?= $i ?>" class="<?= $i === 0 ? '' : 'hidden' ?>">
                    <p class="mt-6 max-w-[620px] text-base leading-7 text-ink/75"><?= esc($t['texto']) ?></p>
                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        <?php foreach ($t['items'] as $it) : ?>
                            <li class="flex items-center gap-2 font-medium text-esm-900"><?= icono('check', 18, 'shrink-0 text-esm-700') ?> <?= esc($it) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php $i++; endforeach; ?>
            <div class="mt-8 flex flex-wrap items-center gap-5">
                <a href="#registro" class="rounded-full bg-esm-800 px-[30px] py-[18px] font-semibold text-white hover:bg-esm-700">Inscríbete</a>
                <a href="mailto:<?= esc($c->correo, 'attr') ?>" class="flex items-center gap-3 rounded-[30px] bg-esm-50 px-5 py-3">
                    <?= icono('mail', 24, 'text-esm-800') ?>
                    <span class="text-sm"><span class="block text-ink/60">¿Necesitas ayuda?</span><span class="font-semibold text-esm-900">Escríbenos</span></span>
                </a>
            </div>
        </div>
    </div>
</section>

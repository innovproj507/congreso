<?php
$items = [
    ['tab' => 'Conferencias magistrales', 'titulo' => 'Tres días de ciencia pediátrica al más alto nivel',
        'texto' => 'Conferencias magistrales de 9:00 a. m. a 4:00 p. m. aproximadamente, con expertos nacionales e internacionales.',
        'puntos' => ['Del 17 al 19 de junio de 2027', 'Megapolis Convention Center', 'Actualización para toda la pediatría']],
    ['tab' => 'Box lunch diario', 'titulo' => 'Un almuerzo diario incluido',
        'texto' => 'Cada día de congreso incluye un almuerzo tipo “box lunch” para que no pierdas ninguna sesión.',
        'puntos' => ['1 almuerzo por día', 'Entrega práctica en sitio', 'Incluido en tu inscripción']],
    ['tab' => 'Premio Protegiendo el Futuro', 'titulo' => 'Trabajos libres con reconocimiento',
        'texto' => 'Presenta tu investigación ante el Comité Científico, con el patrocinio exclusivo de Baby Castille.',
        'puntos' => ['Presentaciones abiertas a los participantes', 'Jurado de profesores', 'Próximamente: requisitos']],
    ['tab' => 'Participantes internacionales', 'titulo' => 'Paquetes de viaje con inscripción incluida',
        'texto' => 'Con Grand Tours gestiona hoteles, tiquetes aéreos y traslados en un solo lugar.',
        'puntos' => ['Hoteles y traslados', 'Tiquetes aéreos', 'Paquetes con inscripción']],
];
?>
<section id="experiencia" class="bg-white pt-20 lg:pt-[115px]">
    <div class="mx-auto max-w-[1320px] px-5">
        <div class="reveal mb-12 text-center lg:mb-14">
            <p class="font-display text-base font-bold uppercase leading-none text-esm-700 sm:text-xl">Lo que vivirás</p>
            <h2 class="font-display mx-auto mt-3 max-w-3xl text-[26px] font-medium leading-[1.4] sm:text-[40px] sm:leading-[55px] xl:text-[52px] xl:leading-[1.25]">Una experiencia académica completa</h2>
        </div>
        <div class="bg-gem flex flex-col items-center gap-8 rounded-[30px] px-4 py-20 text-white lg:flex-row lg:gap-12 lg:py-0 lg:pl-10 lg:pr-0" data-tabs>
            <ul class="flex w-full flex-col gap-5 lg:max-w-[400px] lg:py-14" role="tablist">
                <?php foreach ($items as $i => $x) : ?>
                    <li>
                        <button type="button" role="tab" data-tab="<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                            class="exp-tab font-display flex w-full items-center justify-between rounded-full py-1 pl-6 pr-1 text-left text-lg transition lg:text-2xl <?= $i === 0 ? 'is-on' : '' ?>">
                            <?= esc($x['tab']) ?>
                            <span class="ml-3 grid h-[55px] w-[55px] shrink-0 place-items-center rounded-full bg-white text-esm-900"><?= icono('arrow-up-right', 22) ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="w-full flex-1">
                <?php foreach ($items as $i => $x) : ?>
                    <div data-panel="<?= $i ?>" class="<?= $i === 0 ? '' : 'hidden' ?> grid w-full items-center gap-8 md:grid-cols-2 lg:pr-0">
                        <div>
                            <h3 class="font-display text-[26px] font-medium leading-[1.33] lg:text-[30px]"><?= esc($x['titulo']) ?></h3>
                            <p class="mt-4 text-base text-esm-100/90"><?= esc($x['texto']) ?></p>
                            <ul class="mt-5 space-y-3">
                                <?php foreach ($x['puntos'] as $p) : ?>
                                    <li class="flex items-center gap-3 font-medium"><span class="grid h-6 w-6 place-items-center rounded-full bg-gold/25 text-gold"><?= icono('check', 14) ?></span> <?= esc($p) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="bg-placeholder min-h-[320px] rounded-[30px] lg:h-full"></div><?php /* PLACEHOLDER: imagen por categoría */ ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

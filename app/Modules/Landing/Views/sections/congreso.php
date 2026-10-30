<?php
$features = [
    ['gem', 'Edición Esmeralda', 'Una gema que simboliza renovación, crecimiento y esperanza.'],
    ['globe', 'Ciencia sin fronteras', 'Expertos y participantes de Panamá y del mundo.'],
    ['shield-check', 'Protegiendo el futuro', 'El conocimiento que compartimos, el futuro que protegemos.'],
];
?>
<?= seccion_inicio('congreso', 'Conoce el Congreso 2027', 'Nuestra celebración más brillante', true) ?>
<div class="grid items-center gap-10 md:grid-cols-2">
    <div class="reveal relative">
        <?php /* Gema decorativa en SVG (reemplaza la escena WebGL del demo en Next) */ ?>
        <svg viewBox="0 0 400 400" class="mx-auto h-[360px] w-full max-w-[420px] sm:h-[460px]" role="img" aria-label="Gema esmeralda">
            <defs>
                <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#5fd0b8"/><stop offset="1" stop-color="#0e6b5c"/></linearGradient>
                <linearGradient id="g2" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#0a2a5c"/><stop offset="1" stop-color="#1fa88c"/></linearGradient>
            </defs>
            <g class="gem-float">
                <polygon points="200,40 320,120 200,360 80,120" fill="url(#g1)" stroke="#8fd3e8" stroke-width="2"/>
                <polygon points="200,40 260,120 200,360 140,120" fill="url(#g2)" opacity=".75"/>
                <polygon points="80,120 140,120 200,40" fill="#8fd3e8" opacity=".35"/>
                <polygon points="320,120 260,120 200,40" fill="#ffffff" opacity=".18"/>
                <polyline points="80,120 320,120" stroke="#8fd3e8" stroke-width="2" fill="none"/>
            </g>
            <circle cx="70" cy="70" r="4" fill="#5fd0b8"/><circle cx="340" cy="80" r="3" fill="#8fd3e8"/><circle cx="355" cy="260" r="4" fill="#5fd0b8"/><circle cx="55" cy="300" r="3" fill="#8fd3e8"/>
        </svg>
        <div class="absolute bottom-4 left-0 rounded-[30px] bg-white/10 px-6 py-4 backdrop-blur">
            <p class="font-display text-5xl font-bold text-gold">55+</p>
            <p class="text-sm text-esm-100">años de trayectoria</p>
        </div>
    </div>
    <div class="reveal">
        <p class="text-lg leading-relaxed text-esm-100">Cumplimos <strong class="text-gold">55 años</strong> y lo celebramos con el LV Congreso Nacional de Pediatría: tres días, del 17 al 19 de junio de 2027, en el Megapolis Convention Center.</p>
        <ul class="mt-8 space-y-5">
            <?php foreach ($features as [$ico, $t, $d]) : ?>
                <li class="flex gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-[30px] bg-gold/20 text-gold"><?= icono($ico) ?></span>
                    <span><span class="block font-semibold"><?= esc($t) ?></span><span class="text-esm-100/80"><?= esc($d) ?></span></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?= seccion_fin() ?>

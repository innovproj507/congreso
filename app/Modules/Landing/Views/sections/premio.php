<?= seccion_inicio('premio', 'Trabajos libres', 'Premio Protegiendo el Futuro', true) ?>
<div class="reveal mx-auto max-w-3xl">
    <p class="mx-auto mb-8 w-fit rounded-full bg-gold px-5 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-esm-900">Próximamente</p>
    <p class="text-center text-lg leading-relaxed text-esm-100">De la mano de <strong class="text-gold">Baby Castille</strong>, aliado estratégico y patrocinador exclusivo de estos premios, promovemos la investigación, el intercambio de conocimientos y la excelencia académica, reconociendo el talento y contribuyendo al desarrollo de la pediatría.</p>
    <p class="mt-4 text-center text-esm-100">Las presentaciones serán abiertas a los participantes del congreso y evaluadas por un jurado de profesores del Comité Científico, que seleccionará los trabajos ganadores.</p>
    <div class="mt-10 rounded-[30px] border border-white/15 bg-white/5 p-7">
        <h3 class="font-display flex items-center gap-2 text-xl font-bold text-gold"><?= icono('award', 22) ?> Requisitos</h3>
        <ul class="mt-4 space-y-2 text-esm-100">
            <?php foreach ($c->requisitosTrabajos as $r) : ?>
                <li class="flex gap-2"><span class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span><?= esc($r) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="mt-4 text-sm text-esm-300">Inscripción de trabajos: <a class="underline" href="mailto:<?= esc($c->correo, 'attr') ?>"><?= esc($c->correo) ?></a></p>
    </div>
    <div class="mt-8 text-center">
        <div class="mx-auto w-fit rounded-[30px] bg-white p-4 shadow-lg">
            <img src="<?= base_url('img/logo-babycastille.webp') ?>" width="326" height="291" loading="lazy" decoding="async" alt="Baby Castille, patrocinador exclusivo del Premio Protegiendo el Futuro" class="h-24 w-auto sm:h-28">
        </div>
        <p class="mt-2 text-sm italic text-esm-300">Comprometidos con el conocimiento y el futuro de la pediatría.</p>
    </div>
</div>
<?= seccion_fin() ?>

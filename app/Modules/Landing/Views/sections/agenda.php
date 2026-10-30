<?= seccion_inicio('agenda', 'Programa', 'Agenda Académica', false, true) ?>
<div class="reveal mx-auto mb-10 w-fit rounded-full bg-esm-800 px-5 py-1.5 text-xs font-bold uppercase tracking-[0.25em] text-white">Próximamente</div>
<div class="grid gap-4 md:grid-cols-3">
    <?php foreach ($c->agenda as $i => $a) : ?>
        <div class="reveal rounded-[30px] border border-esm-100 bg-white p-7 text-center" style="transition-delay:<?= $i * 0.1 ?>s">
            <p class="font-display text-xl font-bold text-esm-800"><?= esc($a['dia']) ?></p>
            <p class="mt-1 text-sm text-ink/60">9:00 a. m. – 4:00 p. m. aprox.</p>
            <p class="mt-4 text-ink/80"><?= esc($a['nota']) ?></p>
            <p class="mt-3 text-xs uppercase tracking-widest text-esm-700">Detalle próximamente</p>
        </div>
    <?php endforeach; ?>
</div>
<?= seccion_fin() ?>

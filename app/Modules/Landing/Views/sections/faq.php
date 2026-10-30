<?= seccion_inicio('faq', 'Preguntas frecuentes', 'Resolvemos tus dudas', false, true) ?>
<div class="mx-auto max-w-3xl space-y-3">
    <?php foreach ($c->faqs as $i => $f) : ?>
        <details class="reveal group rounded-[30px] bg-white shadow-sm" <?= $i === 0 ? 'open' : '' ?> style="transition-delay:<?= $i * 0.05 ?>s">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 text-left font-semibold text-esm-900 [&::-webkit-details-marker]:hidden">
                <?= esc($f['q']) ?>
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-esm-800 text-white">
                    <span class="group-open:hidden"><?= icono('plus', 16) ?></span><span class="hidden group-open:inline"><?= icono('minus', 16) ?></span>
                </span>
            </summary>
            <p class="px-5 pb-5 text-ink/75"><?= esc($f['a']) ?></p>
        </details>
    <?php endforeach; ?>
</div>
<?= seccion_fin() ?>

<?php /* Franja «Apoyan:» con los logos de los patrocinadores, centrados y sin repetir. Los logos están en public/img; para sumar otro, añádelo a $aliados. */
$aliados = [
    ['img/logo-babycastille.webp', 326, 291, 'Baby Castille', null, 'h-[72px]'],
];
?>
<section class="border-y border-esm-100 bg-white py-10" aria-label="Apoyan">
    <p class="mb-6 text-center text-sm font-semibold uppercase tracking-[0.25em] text-esm-700">Apoyan:</p>
    <div class="mx-auto flex max-w-[1320px] flex-wrap items-center justify-center gap-6 px-5">
        <?php foreach ($aliados as [$src, $w, $h, $nombre, $url, $alto]) : ?>
            <?php $img = '<img src="' . base_url($src) . '" width="' . $w . '" height="' . $h . '" loading="lazy" decoding="async" alt="' . esc($nombre, 'attr') . '" class="' . $alto . ' w-auto object-contain">'; ?>
            <?php if ($url) : ?>
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= esc($nombre, 'attr') ?> (se abre en una pestaña nueva)" class="grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-10 py-3"><?= $img ?></a>
            <?php else : ?>
                <span class="grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-10 py-3"><?= $img ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

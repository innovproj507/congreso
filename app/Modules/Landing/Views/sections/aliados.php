<?php /* Franja de aliados con desplazamiento continuo. Los logos están en public/img. */
$aliados = [
    ['img/logo.webp', 394, 78, 'Sociedad Panameña de Pediatría', null, 'h-12'],
    ['img/logo-babycastille.webp', 326, 291, 'Baby Castille', null, 'h-[72px]'],
];
$fila = array_merge($aliados, $aliados, $aliados, $aliados);
?>
<section class="overflow-hidden border-y border-esm-100 bg-white py-10" aria-label="Aliados estratégicos">
    <p class="mb-6 text-center text-sm font-semibold uppercase tracking-[0.25em] text-esm-700">Aliados estratégicos del congreso</p>
    <div class="marquee flex w-max gap-6">
        <?php foreach (array_merge($fila, $fila) as $i => [$src, $w, $h, $nombre, $url, $alto]) : $oculto = $i >= count($aliados); // solo la primera tanda se anuncia a lectores de pantalla ?>
            <?php $img = '<img src="' . base_url($src) . '" width="' . $w . '" height="' . $h . '" loading="lazy" decoding="async" alt="' . ($oculto ? '' : esc($nombre, 'attr')) . '" class="' . $alto . ' w-auto object-contain">'; ?>
            <?php if ($url) : ?>
                <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" <?= $oculto ? 'tabindex="-1" aria-hidden="true"' : 'aria-label="' . esc($nombre, 'attr') . ' (se abre en una pestaña nueva)"' ?> class="grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-10 py-3"><?= $img ?></a>
            <?php else : ?>
                <span <?= $oculto ? 'aria-hidden="true"' : '' ?> class="grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-10 py-3"><?= $img ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

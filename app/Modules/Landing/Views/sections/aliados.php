<?php /* Franja «Apoyan:» con los logos de los patrocinadores, en carrusel continuo (se pausa al pasar el cursor). Los logos están en public/img; para sumar otro, añádelo a $aliados. */
$aliados = [
    ['img/logo-babycastille.webp', 310, 275, 'Baby Castille', null, 'h-[46px] sm:h-[72px]'],
    ['img/logo-mivo.webp', 445, 214, 'MIVO · Mis vacunas online', null, 'h-[46px] sm:h-[72px]'],
    ['img/logo-nicklaus.webp', 378, 203, 'Nicklaus Children’s Hospital Global Health', null, 'h-[46px] sm:h-[72px]'],
    ['img/logo-om-pharma.webp', 307, 221, 'OM Pharma', null, 'h-[46px] sm:h-[72px]'],
    ['img/logo-opella.webp', 590, 175, 'Opella', null, 'h-[36px] sm:h-[56px]'],
    ['img/logo-raven.webp', 558, 176, 'Raven Productos Farmacéuticos', null, 'h-[38px] sm:h-[60px]'],
    ['img/logo-altosdemaria.webp', 417, 213, 'Altos del María', null, 'h-[42px] sm:h-[66px]'],
    ['img/logo-cevaxin.webp', 481, 186, 'Cevaxin', null, 'h-[36px] sm:h-[56px]'],
    ['img/logo-designer.webp', 231, 200, 'Designers Pop Up Shop', null, 'h-[46px] sm:h-[72px]'],
    ['img/logo-els.webp', 224, 210, 'ELS', null, 'h-[44px] sm:h-[68px]'],
    ['img/logo-fopa.webp', 517, 161, 'FOPA · Ayudamos a comunicarte', null, 'h-[34px] sm:h-[52px]'],
    ['img/logo-gistagrip.webp', 292, 207, 'Hista Grip', null, 'h-[44px] sm:h-[68px]'],
    ['img/logo-lucera.webp', 530, 158, 'Lucera', null, 'h-[34px] sm:h-[52px]'],
    ['img/logo-neilmed.webp', 513, 95, 'NeilMed', null, 'h-[26px] sm:h-[40px]'],
    ['img/logo-newport.webp', 526, 164, 'Newport Pharmaceuticals International', null, 'h-[36px] sm:h-[56px]'],
    ['img/logo-exceltis.webp', 470, 204, 'Exeltis', null, 'h-[40px] sm:h-[62px]'],
    ['img/logo-pediatrics.webp', 239, 225, 'ABC Pediatrics · Clínica de Especialidades Pediátricas', null, 'h-[44px] sm:h-[68px]'],
    ['img/logo-respirator.webp', 498, 149, 'Respirator Care Panamá', null, 'h-[34px] sm:h-[52px]'],
]; // [archivo, ancho, alto, nombre, enlace (o null), clase de altura]
?>
<section class="overflow-hidden border-y border-esm-100 bg-white py-10" aria-label="Apoyan">
    <p class="mb-6 text-center text-sm font-semibold uppercase tracking-[0.25em] text-esm-700">Apoyan:</p>
    <?php /* Carrusel: la tanda se repite 2 veces (las copias van ocultas a lectores de pantalla) y la animación recorre la mitad de la pista, así el bucle no tiene saltos. */ ?>
    <div class="carrusel-logos flex w-max">
        <?php for ($copia = 0; $copia < 2; $copia++) : $oculto = $copia > 0; ?>
            <?php foreach ($aliados as [$src, $w, $h, $nombre, $url, $alto]) : ?>
                <?php $img = '<img src="' . base_url($src) . '" width="' . $w . '" height="' . $h . '" loading="lazy" decoding="async" alt="' . ($oculto ? '' : esc($nombre, 'attr')) . '" class="' . $alto . ' w-auto object-contain">'; ?>
                <?php if ($url) : ?>
                    <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer" <?= $oculto ? 'tabindex="-1" aria-hidden="true"' : 'aria-label="' . esc($nombre, 'attr') . ' (se abre en una pestaña nueva)"' ?> class="mr-4 grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-4 py-3 sm:mr-6 sm:px-8"><?= $img ?></a>
                <?php else : ?>
                    <span <?= $oculto ? 'aria-hidden="true"' : '' ?> class="mr-4 grid shrink-0 place-items-center rounded-[30px] border border-esm-100 bg-white px-4 py-3 sm:mr-6 sm:px-8"><?= $img ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</section>

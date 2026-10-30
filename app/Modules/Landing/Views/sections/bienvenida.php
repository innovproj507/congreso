<?php
/* Carta de bienvenida. Escritorio: spp/carta.jpg · Celular: spp/carta_m.jpg (versión vertical).
 * El texto completo queda disponible como texto: «Leer la carta como texto» en celular y para lectores de pantalla en escritorio. */
$parrafos = [
    'Es un honor y una gran alegría darles la más cordial bienvenida al 55.º Congreso Nacional de Pediatría 2027 Edición Esmeralda, un espacio de encuentro que reúne a profesionales comprometidos con la salud, el bienestar y el futuro de nuestros niños, niñas y adolescentes.',
    'Este congreso representa una valiosa oportunidad para compartir conocimientos, actualizar nuestras prácticas, intercambiar experiencias y fortalecer los lazos que nos unen como comunidad pediátrica.',
    'A través de un programa científico y académico de excelencia, abordaremos los avances, desafíos y nuevas perspectivas que contribuyen a mejorar la atención integral de la población infantil.',
    'En esta edición, reafirmamos nuestro compromiso con una pediatría innovadora, humana y basada en la evidencia científica, promoviendo la participación activa de colegas de distintas especialidades y disciplinas. Creemos firmemente que el trabajo colaborativo, la educación médica continua y el intercambio de ideas son fundamentales para seguir transformando la atención pediátrica en nuestro país.',
    'Les invito a ser parte activa de este importante encuentro, a aprovechar cada oportunidad de aprendizaje y a continuar construyendo juntos una comunidad profesional comprometida con la excelencia, la investigación y la defensa de la salud infantil.',
    'Será un privilegio contar con su presencia y compartir esta experiencia con todos ustedes.',
];
ob_start();
?>
<h2 class="font-display text-[26px] font-bold uppercase tracking-wide text-esm-800">Carta de bienvenida</h2>
<p class="mt-4 font-semibold">Estimados colegas y miembros de la comunidad pediátrica:</p>
<?php foreach ($parrafos as $p) : ?><p class="mt-4 leading-7 text-ink/80"><?= esc($p) ?></p><?php endforeach; ?>
<p class="mt-4 font-semibold">¡Bienvenidos al 55.º Congreso Nacional de Pediatría 2027!</p>
<p class="mt-6">Con aprecio y estima,</p>
<p class="font-display mt-2 text-lg font-bold text-esm-800">Dra. Geraldine Norte</p>
<p>Presidenta<br>Sociedad Panameña de Pediatría.<br>“Protegiendo el Futuro con Ciencia, Sin Fronteras”.</p>
<?php $textoCarta = ob_get_clean(); ?>
<section id="bienvenida" class="bg-white py-12 lg:py-20" aria-label="Carta de bienvenida">
    <div class="mx-auto max-w-[1320px] px-5">
        <div class="reveal mx-auto max-w-[560px] overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100 md:max-w-none">
            <picture>
                <source media="(max-width: 767px)" srcset="<?= base_url('img/carta-bienvenida-movil.webp') ?>" width="1000" height="1200">
                <img src="<?= base_url('img/carta-bienvenida.webp') ?>" width="2000" height="1079" loading="lazy" decoding="async"
                    alt="Carta de bienvenida de la Dra. Geraldine Norte, presidenta de la Sociedad Panameña de Pediatría, al 55.º Congreso Nacional de Pediatría 2027, Edición Esmeralda."
                    class="block h-auto w-full">
            </picture>
        </div>

        <?php /* Celular: el texto de la carta se abre aparte, por si la imagen se ve pequeña */ ?>
        <details class="mx-auto mt-4 max-w-[560px] rounded-2xl bg-esm-50 p-4 text-sm text-ink md:hidden">
            <summary class="cursor-pointer font-semibold text-esm-800">Leer la carta como texto</summary>
            <div class="mt-3"><?= $textoCarta ?></div>
        </details>
        <?php /* Escritorio: texto solo para lectores de pantalla */ ?>
        <div class="hidden md:block md:sr-only"><?= $textoCarta ?></div>
    </div>
</section>

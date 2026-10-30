<?php
/* Carta de bienvenida. En pantallas medianas y grandes se muestra el arte oficial (spp/CARTA BIENVENIDA.jpg);
 * en celular, donde el texto del arte sería ilegible, se muestra el mismo texto en una tarjeta.
 * En escritorio el texto queda disponible para lectores de pantalla (md:sr-only). */
$parrafos = [
    'Es un honor y una gran alegría darles la más cordial bienvenida al 55.º Congreso Nacional de Pediatría 2027 Edición Esmeralda, un espacio de encuentro que reúne a profesionales comprometidos con la salud, el bienestar y el futuro de nuestros niños, niñas y adolescentes.',
    'Este congreso representa una valiosa oportunidad para compartir conocimientos, actualizar nuestras prácticas, intercambiar experiencias y fortalecer los lazos que nos unen como comunidad pediátrica.',
    'A través de un programa científico y académico de excelencia, abordaremos los avances, desafíos y nuevas perspectivas que contribuyen a mejorar la atención integral de la población infantil.',
    'En esta edición, reafirmamos nuestro compromiso con una pediatría innovadora, humana y basada en la evidencia científica, promoviendo la participación activa de colegas de distintas especialidades y disciplinas. Creemos firmemente que el trabajo colaborativo, la educación médica continua y el intercambio de ideas son fundamentales para seguir transformando la atención pediátrica en nuestro país.',
    'Les invito a ser parte activa de este importante encuentro, a aprovechar cada oportunidad de aprendizaje y a continuar construyendo juntos una comunidad profesional comprometida con la excelencia, la investigación y la defensa de la salud infantil.',
    'Será un privilegio contar con su presencia y compartir esta experiencia con todos ustedes.',
];
?>
<section id="bienvenida" class="bg-white py-12 lg:py-20" aria-labelledby="carta-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <div class="reveal overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100">
            <img src="<?= base_url('img/carta-bienvenida.webp') ?>" width="2000" height="1079" loading="lazy" decoding="async"
                alt="Carta de bienvenida de la Dra. Geraldine Norte, presidenta de la Sociedad Panameña de Pediatría, al 55.º Congreso Nacional de Pediatría 2027, Edición Esmeralda."
                class="hidden h-auto w-full md:block">

            <article class="bg-gem p-6 text-white md:sr-only sm:p-8">
                <h2 id="carta-titulo" class="font-display text-[28px] font-bold uppercase tracking-wide text-gold">Carta de bienvenida</h2>
                <p class="mt-4 font-semibold">Estimados colegas y miembros de la comunidad pediátrica:</p>
                <?php foreach ($parrafos as $p) : ?><p class="mt-4 leading-7 text-esm-100"><?= esc($p) ?></p><?php endforeach; ?>
                <p class="mt-4 font-semibold">¡Bienvenidos al 55.º Congreso Nacional de Pediatría 2027!</p>
                <p class="mt-6 text-esm-100">Con aprecio y estima,</p>
                <p class="font-display mt-2 text-lg font-bold text-gold">Dra. Geraldine Norte</p>
                <p class="text-esm-100">Presidenta<br>Sociedad Panameña de Pediatría.<br>“Protegiendo el Futuro con Ciencia, Sin Fronteras”.</p>
            </article>
        </div>
    </div>
</section>

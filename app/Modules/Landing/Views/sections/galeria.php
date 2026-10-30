<?php
/**
 * Galería dinámica "Revive el Congreso 2026".
 * Diseño Bento + Presentación animada con Ken Burns + Mosaico filtrable (sin scroll horizontal).
 * Se alimenta automáticamente de spp/galeria/ mediante GaleriaService.
 *
 * @var array{fotos:list<array>,video:?array} $galeria
 */
$fotos = $galeria['fotos'] ?? [];
$video = $galeria['video'] ?? null;
$total = count($fotos);
if ($total === 0 && ! $video) {
    return;
}

$primera = $fotos[0] ?? null;
$inicialVisibles = 8;

$datosJs = [
    'fotos' => array_map(static fn ($f) => [
        'src'    => base_url($f['src']),
        'thumb'  => base_url($f['thumb']),
        'w'      => $f['width'],
        'h'      => $f['height'],
        'alt'    => $f['alt'],
        'titulo' => $f['titulo'] ?? '',
        'orient' => ($f['height'] > $f['width']) ? 'vertical' : 'horizontal',
    ], $fotos),
    'video' => $video ? [
        'src'    => base_url($video['src']),
        'tipo'   => $video['tipo'],
        'poster' => $video['poster'] ? base_url($video['poster']) : '',
        'titulo' => $video['titulo'],
    ] : null,
];
?>
<section id="revive" class="bg-esm-50 pb-20 pt-10 lg:pb-28 lg:pt-14" aria-labelledby="revive-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <!-- Encabezado -->
        <div class="reveal flex flex-col items-center text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-esm-800/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-esm-800">
                <?= icono('sparkles', 15, 'text-esm-800') ?> Edición 2026 · Galería oficial
            </span>
            <h2 id="revive-titulo" class="font-display mx-auto mt-3 max-w-4xl text-[34px] font-bold leading-[1.12] tracking-tight text-esm-900 sm:text-[50px] xl:text-[60px]">
                Revive el Congreso 2026
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-base text-ink/75 sm:text-lg">
                Conferencias magistrales, reencuentro entre colegas y momentos inolvidables de nuestra última edición.
            </p>
        </div>

        <!-- ESCENARIO PRINCIPAL (Bento Showcase: Visor Animado + Video Oficial) -->
        <div class="mt-10 grid gap-5 lg:grid-cols-12">
            <?php if ($primera) : ?>
                <div class="<?= $video ? 'lg:col-span-8' : 'lg:col-span-12' ?> group relative overflow-hidden rounded-[28px] bg-esm-900 shadow-xl ring-1 ring-esm-900/10">
                    <!-- Contenedor de proporción 16:10 / 16:9 -->
                    <div id="visor-escenario" class="relative aspect-[16/11] w-full cursor-pointer overflow-hidden sm:aspect-[16/9]" data-abrir-visor="1">
                        <!-- Fondo desenfocado ambiental para fotos verticales -->
                        <img id="visor-fondo" src="<?= base_url($primera['thumb']) ?>" alt="" aria-hidden="true"
                            class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-35 blur-2xl transition-opacity duration-700">

                        <!-- Doble capa para fundido suave (crossfade + Ken Burns) -->
                        <img id="visor-img-a" src="<?= base_url($primera['src']) ?>" alt="<?= esc($primera['alt'], 'attr') ?>"
                            class="visor-capa is-activa absolute inset-0 h-full w-full object-cover">
                        <img id="visor-img-b" src="<?= base_url($primera['src']) ?>" alt=""
                            class="visor-capa absolute inset-0 h-full w-full object-cover">

                        <!-- Sombra degradada superior e inferior -->
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-esm-900/60 to-transparent" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-esm-900/85 via-esm-900/35 to-transparent" aria-hidden="true"></div>

                        <!-- Etiqueta superior izquierda -->
                        <div class="absolute left-4 top-4 z-10 flex items-center gap-2.5 rounded-full bg-esm-900/65 px-3.5 py-1.5 text-xs font-semibold text-white backdrop-blur-md sm:left-6 sm:top-5">
                            <span id="visor-punto" class="h-2 w-2 rounded-full bg-gold animate-pulse"></span>
                            <span id="visor-estado">Presentación automática</span>
                            <span class="text-white/40">·</span>
                            <span id="visor-num" class="tabular-nums text-gold">01 / <?= str_pad((string) $total, 2, '0', STR_PAD_LEFT) ?></span>
                        </div>

                        <!-- Botón pantalla completa superior derecha -->
                        <button type="button" id="visor-ampliar" aria-label="Ver fotografía en pantalla completa"
                            class="absolute right-4 top-4 z-10 inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-white/30 sm:right-6 sm:top-5">
                            <?= icono('arrow-up-right', 16) ?>
                            <span class="hidden sm:inline">Pantalla completa</span>
                        </button>

                        <!-- Barra inferior de controles y progreso -->
                        <div class="absolute inset-x-0 bottom-0 z-10 flex flex-col gap-3 p-4 sm:p-6" onclick="event.stopPropagation()">
                            <!-- Barra de progreso animada -->
                            <div class="h-1 w-full overflow-hidden rounded-full bg-white/20">
                                <div id="visor-barra" class="h-full w-0 rounded-full bg-gold"></div>
                            </div>

                            <div class="flex items-center justify-between gap-4 text-white">
                                <p id="visor-caption" class="truncate text-sm font-medium text-white/95 sm:text-base">
                                    <?= esc($primera['titulo'] ?: 'Congreso Nacional de Pediatría 2026 · Momento 1') ?>
                                </p>

                                <div class="flex shrink-0 items-center gap-1.5">
                                    <button type="button" id="visor-prev" aria-label="Foto anterior"
                                        class="grid h-9 w-9 place-items-center rounded-full bg-white/15 text-white backdrop-blur-md transition hover:bg-white/30">
                                        <?= icono('chevron-left', 20) ?>
                                    </button>
                                    <button type="button" id="visor-pausa" aria-label="Pausar presentación" aria-pressed="false"
                                        class="grid h-9 w-9 place-items-center rounded-full bg-white/15 text-white backdrop-blur-md transition hover:bg-white/30">
                                        <span data-ic="pausa"><?= icono('pause', 16) ?></span>
                                        <span data-ic="play" class="hidden"><?= icono('play', 16, 'fill-current') ?></span>
                                    </button>
                                    <button type="button" id="visor-next" aria-label="Foto siguiente"
                                        class="grid h-9 w-9 place-items-center rounded-full bg-white/15 text-white backdrop-blur-md transition hover:bg-white/30">
                                        <?= icono('chevron-right', 20) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tarjeta destacada de Video Oficial -->
            <?php if ($video) : ?>
                <div class="<?= $primera ? 'lg:col-span-4' : 'lg:col-span-12' ?> flex">
                    <button type="button" data-video="1" aria-label="Reproducir <?= esc($video['titulo'], 'attr') ?>"
                        class="group relative flex min-h-[320px] w-full flex-col justify-between overflow-hidden rounded-[28px] bg-esm-900 p-6 text-left text-white shadow-xl ring-1 ring-esm-900/10 sm:p-7">
                        <?php if ($video['poster']) : ?>
                            <img src="<?= base_url($video['poster']) ?>" width="<?= $video['width'] ?>" height="<?= $video['height'] ?>"
                                alt="" loading="lazy" decoding="async"
                                class=" absolute inset-0 h-full w-full object-cover opacity-65 transition-transform duration-700 group-hover:scale-105">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-esm-900 via-esm-900/45 to-esm-900/30" aria-hidden="true"></div>

                        <!-- Etiqueta superior -->
                        <div class="relative z-10 flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 rounded-full bg-gold/20 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-gold ring-1 ring-gold/40 backdrop-blur-md">
                                <?= icono('film', 14) ?> Video Oficial
                            </span>
                            <span class="rounded-full bg-black/40 px-3 py-1 text-xs font-semibold text-white/90 backdrop-blur-md">1:03 min</span>
                        </div>

                        <!-- Botón Play central con onda animada -->
                        <div class="relative z-10 my-auto flex flex-col items-center py-6 text-center">
                            <span class="relative grid h-20 w-20 place-items-center">
                                <span class="absolute inset-0 rounded-full bg-gold/35 animate-ping"></span>
                                <span class="relative grid h-16 w-16 place-items-center rounded-full bg-gold text-esm-900 shadow-2xl transition-transform duration-300 group-hover:scale-110">
                                    <?= icono('play', 28, 'fill-current translate-x-0.5') ?>
                                </span>
                            </span>
                            <span class="mt-4 text-xs font-bold uppercase tracking-widest text-gold">Reproducir con sonido</span>
                        </div>

                        <!-- Pie de la tarjeta de video -->
                        <div class="relative z-10">
                            <h3 class="font-display text-xl font-bold leading-snug text-white sm:text-2xl">
                                <?= esc($video['titulo']) ?>
                            </h3>
                            <p class="mt-1 text-sm text-esm-100/85">
                                Mira el resumen audiovisual de las jornadas académicas y actividades.
                            </p>
                        </div>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- MOSAICO DE MINIATURAS (Sin scroll horizontal) -->
        <?php if ($total > 1) : ?>
            <div class="mt-10">
                <!-- Barra de filtros y contador -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-esm-100 pb-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-esm-900">
                        <?= icono('layout-grid', 18, 'text-esm-700') ?>
                        <span>Explora la galería fotográfica</span>
                        <span class="rounded-full bg-esm-800/10 px-2.5 py-0.5 text-xs font-bold text-esm-800"><?= $total ?> fotos</span>
                    </div>

                    <div class="inline-flex rounded-full bg-white p-1 shadow-xs ring-1 ring-esm-100" role="group" aria-label="Filtrar fotografías">
                        <button type="button" data-filtro="todas" class="vista-btn is-on rounded-full px-4 py-1.5 text-xs font-semibold transition sm:text-sm">
                            Todas (<?= $total ?>)
                        </button>
                        <button type="button" data-filtro="horizontal" class="vista-btn rounded-full px-4 py-1.5 text-xs font-semibold transition sm:text-sm">
                            Panorámicas
                        </button>
                        <button type="button" data-filtro="vertical" class="vista-btn rounded-full px-4 py-1.5 text-xs font-semibold transition sm:text-sm">
                            Retratos
                        </button>
                    </div>
                </div>

                <!-- Grid limpio de tarjetas -->
                <div id="galeria-grid" class="mt-6 grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-4 sm:gap-5">
                    <?php foreach ($fotos as $idx => $f) :
                        $orient = ($f['height'] > $f['width']) ? 'vertical' : 'horizontal';
                        $ocultaInicial = $idx >= $inicialVisibles;
                    ?>
                        <button type="button"
                            data-foto="<?= $idx ?>"
                            data-orient="<?= $orient ?>"
                            data-extra="<?= $ocultaInicial ? '1' : '0' ?>"
                            aria-label="Ver fotografía <?= $idx + 1 ?> en grande"
                            class="galeria-item group relative aspect-[4/3] w-full overflow-hidden rounded-2xl bg-esm-100 shadow-xs ring-1 ring-esm-900/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none <?= $ocultaInicial ? 'hidden' : '' ?> <?= $idx === 0 ? 'is-activa' : '' ?>">
                            <img src="<?= base_url($f['thumb']) ?>" width="<?= $f['width'] ?>" height="<?= $f['height'] ?>"
                                alt="<?= esc($f['alt'], 'attr') ?>" loading="lazy" decoding="async"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <span class="absolute inset-0 bg-gradient-to-t from-esm-900/75 via-esm-900/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                            <span class="absolute bottom-2.5 left-3 right-3 flex items-center justify-between text-white opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <span class="rounded-full bg-black/45 px-2.5 py-0.5 text-xs font-bold backdrop-blur-xs">#<?= str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="grid h-7 w-7 place-items-center rounded-full bg-white text-esm-900 shadow"><?= icono('arrow-up-right', 14) ?></span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Botón para desplegar/plegar el resto sin scroll horizontal -->
                <?php if ($total > $inicialVisibles) : ?>
                    <div id="galeria-mas-wrap" class="mt-8 text-center">
                        <button type="button" id="galeria-mas-btn" data-expandido="0"
                            class="inline-flex items-center gap-2 rounded-full border-2 border-esm-800 bg-white px-7 py-3 text-sm font-bold text-esm-800 shadow-xs transition hover:bg-esm-800 hover:text-white">
                            <span id="galeria-mas-txt">Ver las <?= $total ?> fotografías (+<?= $total - $inicialVisibles ?> más)</span>
                            <?= icono('plus', 16) ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- VISOR A PANTALLA COMPLETA (Lightbox) -->
    <div id="lightbox" class="fixed inset-0 z-[100] hidden flex-col items-center justify-between bg-black/95 p-3 backdrop-blur-md sm:p-6" role="dialog" aria-modal="true" aria-label="Visor de la galería">
        <!-- Barra superior del visor -->
        <div class="flex w-full max-w-6xl items-center justify-between text-white">
            <div class="flex items-center gap-3">
                <span id="lb-contador" class="rounded-full bg-white/10 px-3.5 py-1 text-xs font-bold tracking-wider text-gold sm:text-sm" aria-live="polite"></span>
                <span id="lb-titulo" class="font-display truncate text-sm font-medium text-white/90 sm:text-base"></span>
            </div>
            <button type="button" id="lb-cerrar" class="grid h-11 w-11 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/25" aria-label="Cerrar visor">
                <?= icono('x', 22) ?>
            </button>
        </div>

        <!-- Imagen o Video central -->
        <div class="relative my-auto flex max-h-[72vh] w-full max-w-5xl items-center justify-center">
            <button type="button" id="lb-prev" class="absolute left-1 top-1/2 z-20 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur-md transition hover:bg-white/30 sm:-left-4 sm:h-12 sm:w-12" aria-label="Anterior">
                <?= icono('chevron-left', 26) ?>
            </button>

            <img id="lb-img" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt=""
                class="max-h-[72vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl transition-opacity duration-300">
            <video id="lb-video" controls playsinline preload="none"
                class="hidden max-h-[72vh] w-auto max-w-full rounded-2xl bg-black shadow-2xl"></video>

            <button type="button" id="lb-next" class="absolute right-1 top-1/2 z-20 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white backdrop-blur-md transition hover:bg-white/30 sm:-right-4 sm:h-12 sm:w-12" aria-label="Siguiente">
                <?= icono('chevron-right', 26) ?>
            </button>
        </div>

        <!-- Tira de miniaturas en el visor para cambiar rápido -->
        <div id="lb-thumbs" class="flex max-w-full items-center gap-2 overflow-x-auto py-1">
            <?php foreach ($fotos as $idx => $f) : ?>
                <button type="button" data-lb-thumb="<?= $idx ?>" aria-label="Ir a foto <?= $idx + 1 ?>"
                    class="lb-thumb h-12 w-16 shrink-0 overflow-hidden rounded-lg opacity-50 ring-2 ring-transparent transition hover:opacity-90">
                    <img src="<?= base_url($f['thumb']) ?>" alt="" loading="lazy" class="h-full w-full object-cover">
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <script type="application/json" id="datos-galeria"><?= json_encode($datosJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</section>

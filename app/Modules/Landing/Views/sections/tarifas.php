<?php
/**
 * Inscripciones: arte oficial (spp/INSCRIPCIONES.jpg) en pantallas medianas y grandes; en celular, la misma
 * información como tabla de texto armada con las tarifas de la base de datos (siempre iguales al formulario).
 *
 * @var array|null $catalogo
 */
$tarifas = $catalogo['tarifas'] ?? ['preventa' => [], 'regular' => []];
$formas  = $catalogo['formas'] ?? [];
$regulares = array_column($tarifas['regular'], null, 'categoria');
$vigente = $catalogo['vigente'] ?? 'preventa';
?>
<section id="inscripciones" class="bg-esm-50 py-12 lg:py-20" aria-labelledby="insc-titulo">
    <div class="mx-auto max-w-[1320px] px-5">
        <div class="reveal overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100">
            <img src="<?= base_url('img/inscripciones.webp') ?>" width="2400" height="875" loading="lazy" decoding="async"
                alt="Inscríbete ya. Inscripciones y becas. Early Bird del 30 de octubre de 2026 al 15 de marzo de 2027 y tarifa regular desde el 16 de marzo hasta agotar. Medios de pago: Visa, Mastercard, Yappy y Banco Nacional de Panamá."
                class="hidden h-auto w-full md:block">

            <?php /* Versión en texto: visible en celular; en escritorio solo para lectores de pantalla */ ?>
            <div class="bg-white p-5 md:sr-only sm:p-8">
                <h2 id="insc-titulo" class="font-display text-[30px] font-bold leading-tight text-esm-800">Inscríbete ya</h2>
                <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-esm-700">Inscripciones / Becas</p>
                <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-esm-100">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Tarifas de inscripción por tipo de participante</caption>
                        <thead class="bg-esm-800 text-white">
                            <tr>
                                <th scope="col" class="px-3 py-3 font-semibold">Participante</th>
                                <th scope="col" class="px-2 py-3 text-center font-semibold">Early Bird<span class="block text-[10px] font-normal opacity-80">30 oct 2026 – 15 mar 2027</span></th>
                                <th scope="col" class="px-2 py-3 text-center font-semibold">Regular<span class="block text-[10px] font-normal opacity-80">desde el 16 de marzo</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarifas['preventa'] as $t) : $r = $regulares[$t['categoria']] ?? null; ?>
                                <tr class="border-t border-esm-100">
                                    <th scope="row" class="px-3 py-3 font-medium text-ink"><?= esc($t['categoria']) ?></th>
                                    <?php if ($r && $t['centavos'] === 0 && $r['centavos'] === 0) : ?>
                                        <td colspan="2" class="px-2 py-3 text-center font-bold text-esm-800">Sin costo</td>
                                    <?php else : ?>
                                        <td class="px-2 py-3 text-center font-bold text-esm-800 <?= $vigente === 'preventa' ? 'bg-esm-50' : '' ?>"><?= esc($t['precio']) ?></td>
                                        <td class="px-2 py-3 text-center font-bold text-esm-800 <?= $vigente === 'regular' ? 'bg-esm-50' : '' ?>"><?= esc($r['precio'] ?? '—') ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <h3 class="font-display mt-6 text-lg font-bold text-esm-800">Medios de pago</h3>
                <ul class="mt-2 space-y-1 text-sm text-ink/80">
                    <li>Visa y Mastercard</li>
                    <li>Yappy: @sociedadpanamenadepediatria</li>
                    <li>Banco Nacional de Panamá, cuenta corriente de la Sociedad Panameña de Pediatría: 10000354366</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <div class="reveal rounded-[30px] bg-white p-7 shadow-sm">
                <h3 class="font-display flex items-center gap-2 text-xl font-bold text-esm-800"><?= icono('circle-check', 20) ?> ¿Qué incluye?</h3>
                <ul class="mt-4 space-y-3 text-ink/80">
                    <?php foreach ($c->incluye as $i) : ?>
                        <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span><?= esc($i) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="reveal rounded-[30px] bg-white p-7 shadow-sm" style="transition-delay:.1s">
                <h3 class="font-display flex items-center gap-2 text-xl font-bold text-esm-800"><?= icono('credit-card', 20) ?> ¿Cómo se puede pagar?</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($formas as $f) : ?>
                        <span class="rounded-full bg-esm-100 px-4 py-1.5 text-sm font-semibold text-esm-800"><?= esc($f) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="reveal rounded-[30px] bg-white p-7 shadow-sm" style="transition-delay:.2s">
                <h3 class="font-display flex items-center gap-2 text-xl font-bold text-esm-800"><?= icono('mail', 20) ?> ¿Cómo inscribirme?</h3>
                <ol class="mt-4 list-inside list-decimal space-y-2 text-ink/80">
                    <?php foreach ($c->pasosInscripcion as $p) : ?><li><?= esc($p) ?></li><?php endforeach; ?>
                </ol>
                <a href="mailto:<?= esc($c->correo, 'attr') ?>" class="mt-4 block break-all text-sm font-semibold text-esm-700 underline"><?= esc($c->correo) ?></a>
            </div>
        </div>
    </div>
</section>

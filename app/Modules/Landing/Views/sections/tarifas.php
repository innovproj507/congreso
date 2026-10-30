<?php
/**
 * Inscripciones. Escritorio: spp/incripciones.jpg · Celular: spp/incripciones_m.jpg (versión vertical).
 * Las tarifas también se muestran como tabla de texto armada con la base de datos (siempre igual al formulario):
 * en celular dentro de «Ver tarifas como texto» y en escritorio solo para lectores de pantalla.
 *
 * @var array|null $catalogo
 */
$tarifas = $catalogo['tarifas'] ?? ['preventa' => [], 'regular' => []];
$formas  = $catalogo['formas'] ?? [];
$regulares = array_column($tarifas['regular'], null, 'categoria');
$vigente = $catalogo['vigente'] ?? 'preventa';
ob_start();
?>
<h2 class="font-display text-[26px] font-bold leading-tight text-esm-800">Inscripciones / Becas</h2>
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
<?php $textoTarifas = ob_get_clean(); ?>
<section id="inscripciones" class="bg-esm-50 py-12 lg:py-20" aria-label="Inscripciones">
    <div class="mx-auto max-w-[1320px] px-5">
        <div class="reveal mx-auto max-w-[560px] overflow-hidden rounded-[30px] shadow-sm ring-1 ring-esm-100 md:max-w-none">
            <picture>
                <source media="(max-width: 767px)" srcset="<?= base_url('img/inscripciones-movil.webp') ?>" width="1000" height="1200">
                <img src="<?= base_url('img/inscripciones.webp') ?>" width="2400" height="875" loading="lazy" decoding="async"
                    alt="Inscríbete ya. Inscripciones y becas. Early Bird del 30 de octubre de 2026 al 15 de marzo de 2027 y tarifa regular desde el 16 de marzo hasta agotar. Medios de pago: Visa, Mastercard, Yappy y Banco Nacional de Panamá."
                    class="block h-auto w-full">
            </picture>
        </div>

        <details class="mx-auto mt-4 max-w-[560px] rounded-2xl bg-white p-4 text-sm text-ink shadow-sm ring-1 ring-esm-100 md:hidden">
            <summary class="cursor-pointer font-semibold text-esm-800">Ver tarifas como texto</summary>
            <div class="mt-3"><?= $textoTarifas ?></div>
        </details>
        <div class="hidden md:block md:sr-only"><?= $textoTarifas ?></div>

        <div class="mt-8 grid gap-6 md:grid-cols-2">
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
        </div>
    </div>
</section>

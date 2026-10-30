<?php
$pasos = [
    ['list-checks', 'Elige tu categoría', 'Revisa el tarifario vigente (preventa o regular) y selecciona la categoría que te corresponde.'],
    ['credit-card', 'Realiza tu pago', 'Paga con Yappy, Visa, Mastercard, Clave o ACH Banco Nacional.'],
    ['mail-check', 'Confirma tu inscripción', 'Envía tu comprobante y datos a nuestro correo y recibe tu confirmación.'],
];
?>
<?= seccion_inicio('como', 'Inscripción en 3 pasos', 'Cómo inscribirte al congreso', false, true) ?>
<div class="grid gap-[30px] md:grid-cols-3">
    <?php foreach ($pasos as $i => [$ico, $t, $d]) : ?>
        <div class="reveal relative rounded-[30px] bg-white px-[30px] pb-[35px] pt-10 shadow-sm" style="transition-delay:<?= $i * 0.1 ?>s">
            <span class="font-display absolute right-8 top-6 text-[54px] font-bold leading-none text-esm-800/10">0<?= $i + 1 ?></span>
            <span class="grid h-[60px] w-[60px] place-items-center rounded-full bg-esm-800 text-white"><?= icono($ico, 26) ?></span>
            <h3 class="font-display mt-6 text-xl font-medium text-esm-900"><?= esc($t) ?></h3>
            <p class="mt-3 text-base leading-7 text-ink/70"><?= esc($d) ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?= seccion_fin() ?>

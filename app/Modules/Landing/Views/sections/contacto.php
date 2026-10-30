<?php $input = 'w-full rounded-xl border border-esm-100 bg-white px-4 py-3 outline-none focus:border-esm-700'; ?>
<?= seccion_inicio('contacto', 'Contáctanos', '¿Tienes preguntas sobre el congreso?', true) ?>
<div class="grid gap-8 md:grid-cols-2">
    <div class="reveal space-y-6">
        <p class="text-lg text-esm-100">Para consultas o información adicional, escríbenos.</p>
        <a href="mailto:<?= esc($c->correo, 'attr') ?>" class="flex items-center gap-4 rounded-[30px] bg-white/10 p-5"><?= icono('mail', 24, 'text-gold') ?> <span class="break-all font-semibold"><?= esc($c->correo) ?></span></a>
        <p class="flex items-center gap-4 rounded-[30px] bg-white/10 p-5"><?= icono('map-pin', 24, 'text-gold') ?> <span><?= esc($c->sede) ?><br><?= esc($c->fechaTexto) ?></span></p>
    </div>
    <div class="reveal" style="transition-delay:.1s">
        <form id="contacto-form" novalidate class="space-y-4 rounded-[30px] bg-white p-6 text-ink sm:p-8">
            <input name="nombre" required maxlength="100" placeholder="Nombre completo" aria-label="Nombre completo" class="<?= $input ?>">
            <input name="correo" type="email" required maxlength="150" placeholder="Correo electrónico" aria-label="Correo electrónico" class="<?= $input ?>">
            <textarea name="mensaje" required rows="4" maxlength="2000" placeholder="Mensaje" aria-label="Mensaje" class="<?= $input ?>"></textarea>
            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true"><input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></div>
            <p id="contacto-msg" role="status" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></p>
            <button class="w-full rounded-full bg-esm-800 py-3 font-semibold text-white hover:bg-esm-700 disabled:opacity-60">Enviar consulta</button>
        </form>
    </div>
</div>
<?= seccion_fin() ?>

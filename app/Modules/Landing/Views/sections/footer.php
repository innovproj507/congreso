<footer class="bg-[#056a53] pt-16 text-white/90">
    <div class="mx-auto grid max-w-[1320px] gap-10 px-5 md:grid-cols-3">
        <div>
            <img src="<?= base_url('img/logo.webp') ?>" alt="Sociedad Panameña de Pediatría" class="h-14 w-auto rounded-xl bg-white p-2">
            <p class="mt-4 text-sm">Protegiendo el futuro con ciencia. Sin fronteras.</p>
        </div>
        <div>
            <h4 class="font-display mb-4 text-lg font-bold text-white">Enlaces rápidos</h4>
            <ul class="space-y-2 text-sm">
                <?php foreach ($c->menu as $m) : ?><li><a href="#<?= esc($m['id'], 'attr') ?>" class="hover:text-gold"><?= esc($m['label']) ?></a></li><?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4 class="font-display mb-4 text-lg font-bold text-white">Contacto</h4>
            <ul class="space-y-3 text-sm">
                <li class="flex gap-2"><?= icono('mail', 16, 'mt-0.5 shrink-0 text-gold') ?><a class="break-all" href="mailto:<?= esc($c->correo, 'attr') ?>"><?= esc($c->correo) ?></a></li>
                <li class="flex gap-2"><?= icono('map-pin', 16, 'mt-0.5 shrink-0 text-gold') ?><?= esc($c->sede) ?></li>
                <li class="flex gap-2"><?= icono('calendar-days', 16, 'mt-0.5 shrink-0 text-gold') ?><?= esc($c->fechaTexto) ?></li>
            </ul>
        </div>
    </div>
    <div class="mt-12 border-t border-white/20 py-5 text-center text-xs text-white/70">© 2027 Sociedad Panameña de Pediatría · <?= esc($c->nombre) ?> · <?= esc($c->edicion) ?></div>
</footer>

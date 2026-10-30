<div id="std" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 p-5" role="presentation">
    <div role="dialog" aria-modal="true" aria-label="Save the date" class="bg-gem relative w-full max-w-lg rounded-[30px] border border-gold/60 p-8 text-center text-white shadow-2xl sm:p-12">
        <button id="std-cerrar" aria-label="Cerrar" class="absolute right-4 top-4 rounded-full p-2 hover:bg-white/10"><?= icono('x', 20) ?></button>
        <?= icono('calendar-days', 40, 'mx-auto text-gold') ?>
        <p class="mt-4 text-sm font-semibold uppercase tracking-[0.35em] text-esm-300">Save the date</p>
        <p class="font-display mt-3 text-4xl font-bold sm:text-5xl">17 – 19</p>
        <p class="font-display text-2xl sm:text-3xl">de junio de 2027</p>
        <div class="mx-auto my-6 h-px w-20 bg-gold"></div>
        <p class="text-lg font-semibold"><?= esc($c->nombre) ?></p>
        <p class="text-esm-300"><?= esc($c->edicion) ?></p>
        <a id="std-ir" href="#inscripciones" class="mt-8 inline-block rounded-full bg-gold px-[30px] py-[18px] font-semibold text-esm-900 transition hover:brightness-110">Ver inscripciones</a>
    </div>
</div>

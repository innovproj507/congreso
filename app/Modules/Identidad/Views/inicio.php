<?= $this->extend('Modules\Identidad\Views\admin_layout') ?>
<?php $titulo = 'Inicio'; ?>
<?= $this->section('contenido') ?>
<h1 class="font-display text-3xl font-medium text-esm-900">Hola, <?= esc(session('usuario')['nombre']) ?></h1>
<p class="mt-2 text-ink/70">¿Qué quieres hacer hoy?</p>
<div class="mt-8 grid gap-5 md:grid-cols-2">
    <a href="<?= site_url('admin/inscripciones') ?>" class="rounded-[30px] bg-white p-7 shadow-sm transition hover:shadow-md">
        <h2 class="font-display text-xl font-bold text-esm-800">Inscritos</h2>
        <p class="mt-2 text-ink/70">Lista de participantes, estado de pago y exportación.</p>
    </a>
    <a href="<?= esc(rtrim((string) env('tesoreria.panelUrl'), '/'), 'attr') ?>" target="_blank" rel="noopener" class="rounded-[30px] bg-white p-7 shadow-sm transition hover:shadow-md">
        <h2 class="font-display text-xl font-bold text-esm-800">Tesorería ↗</h2>
        <p class="mt-2 text-ink/70">Sistema de tesorería: registrar pagos y emitir recibos.</p>
    </a>
</div>
<?= $this->endSection() ?>

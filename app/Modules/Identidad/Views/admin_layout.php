<?php
// Layout común del panel interno. Cada módulo extiende esta vista: $this->extend('Modules\Identidad\Views\admin_layout')
$u    = session('usuario');
$rol  = $u['rol'] ?? '';
$menu = [
    ['admin',                   'Inicio',        ['administracion', 'gerencia', 'operador']],
    ['admin/inscripciones',     'Inscritos',     ['administracion', 'gerencia', 'operador']],
];
$panelTesoreria = rtrim((string) env('tesoreria.panelUrl'), '/');
$actual = uri_string();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($titulo ?? 'Panel') ?> · Panel SPP</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Ubuntu:wght@500;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/app.css') ?>">
</head>
<body class="min-h-screen bg-esm-50 text-ink">
<header class="bg-esm-900 text-white">
    <div class="mx-auto flex max-w-[1320px] flex-wrap items-center justify-between gap-3 px-5 py-3">
        <a href="<?= site_url('admin') ?>" class="font-display text-lg font-bold">Panel SPP · Congreso 2027</a>
        <nav class="flex flex-wrap items-center gap-1 text-sm">
            <?php foreach ($menu as [$ruta, $texto, $roles]) : if (! in_array($rol, $roles, true)) continue; ?>
                <a href="<?= site_url($ruta) ?>" class="rounded-full px-4 py-2 font-medium hover:bg-white/10 <?= $actual === $ruta || ($ruta !== 'admin' && str_starts_with($actual, $ruta)) ? 'bg-white/15' : '' ?>"><?= $texto ?></a>
            <?php endforeach; ?>
            <?php if ($panelTesoreria) : ?><a href="<?= esc($panelTesoreria, 'attr') ?>" target="_blank" rel="noopener" class="rounded-full px-4 py-2 font-medium hover:bg-white/10">Tesorería ↗</a><?php endif; ?>
        </nav>
        <form method="post" action="<?= site_url('admin/logout') ?>" class="flex items-center gap-3 text-sm">
            <?= csrf_field() ?>
            <span class="text-esm-300"><?= esc($u['nombre'] ?? '') ?> · <?= esc($rol) ?></span>
            <button class="rounded-full border border-white/30 px-4 py-1.5 hover:bg-white/10">Salir</button>
        </form>
    </div>
</header>
<main class="mx-auto max-w-[1320px] px-5 py-8">
    <?php if (session()->getFlashdata('ok')) : ?><p role="status" class="mb-5 rounded-2xl bg-esm-100 px-4 py-3 text-sm font-medium text-esm-800"><?= esc(session()->getFlashdata('ok')) ?></p><?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?><p role="alert" class="mb-5 rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= esc(session()->getFlashdata('error')) ?></p><?php endif; ?>
    <?= $this->renderSection('contenido') ?>
</main>
</body>
</html>

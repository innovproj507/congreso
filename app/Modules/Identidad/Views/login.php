<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ingreso · Panel SPP</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Ubuntu:wght@500;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/app.css') ?>">
</head>
<body class="bg-gem grid min-h-screen place-items-center p-5">
<form method="post" action="<?= site_url('admin/login') ?>" class="w-full max-w-sm rounded-[30px] bg-white p-8 shadow-2xl">
    <?= csrf_field() ?>
    <img src="<?= base_url('img/logo.webp') ?>" alt="Sociedad Panameña de Pediatría" class="mx-auto h-12 w-auto">
    <h1 class="font-display mt-6 text-center text-2xl font-medium text-esm-900">Panel de administración</h1>
    <?php if ($error) : ?><p role="alert" class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= esc($error) ?></p><?php endif; ?>
    <label for="correo" class="mt-6 mb-2 block text-sm font-semibold text-esm-900">Correo</label>
    <input id="correo" name="correo" type="email" required autocomplete="username" autofocus class="w-full rounded-2xl border border-esm-100 px-5 py-3 outline-none focus:border-esm-700">
    <label for="clave" class="mt-4 mb-2 block text-sm font-semibold text-esm-900">Contraseña</label>
    <input id="clave" name="clave" type="password" required autocomplete="current-password" class="w-full rounded-2xl border border-esm-100 px-5 py-3 outline-none focus:border-esm-700">
    <button class="mt-6 w-full rounded-full bg-esm-800 py-3 font-semibold text-white hover:bg-esm-700">Ingresar</button>
</form>
</body>
</html>

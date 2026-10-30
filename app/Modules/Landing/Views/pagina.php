<?php
/**
 * @var \Modules\Landing\Config\Congreso $c
 * @var array|null $catalogo
 */
helper('landing');
$css = FCPATH . 'assets/css/app.css';
$js  = FCPATH . 'assets/js/landing.js';
$datos = [
    'slug'       => $c->slug,
    'endpoint'   => site_url('api/eventos/' . $c->slug . '/inscripciones'),
    'contacto'   => site_url('contacto'),
    'inicio'     => $c->fechaInicioISO,
    'correo'     => $c->correo,
    'grandTours' => $c->grandToursUrl,
    'vigente'    => $catalogo['vigente'] ?? 'preventa',
    'tarifas'    => $catalogo['tarifas'] ?? ['preventa' => [], 'regular' => []],
    'formas'     => $catalogo['formas'] ?? [],
    'instrucciones' => $catalogo['instrucciones'] ?? [],
    'comprobante'   => $catalogo['comprobante'] ?? [],
    'jquery'        => base_url('assets/js/vendor/jquery-3.2.1.min.js'),
    'banco'         => ['beneficiario' => $c->bancoBeneficiario, 'banco' => $c->bancoNombre, 'cuenta' => $c->bancoCuenta],
    'croem'         => \Modules\Eventos\Services\CroemPasarela::configurada() ? \Modules\Eventos\Services\CroemPasarela::paraNavegador() : null,
];
?>
<!doctype html>
<html lang="es" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($c->nombre) ?> 2027 · <?= esc($c->edicion) ?></title>
    <meta name="description" content="<?= esc($c->fechaTexto) ?>, <?= esc($c->sede) ?>. <?= esc($c->lema) ?>.">
    <?php if (env('congreso.indexar') !== 'true') : ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="theme-color" content="#0a2a5c">
    <link rel="canonical" href="<?= esc(base_url(), 'attr') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_PA">
    <meta property="og:site_name" content="Sociedad Panameña de Pediatría">
    <meta property="og:title" content="<?= esc($c->nombre, 'attr') ?> 2027 · <?= esc($c->edicion, 'attr') ?>">
    <meta property="og:description" content="<?= esc($c->fechaTexto, 'attr') ?> · <?= esc($c->sede, 'attr') ?>. <?= esc($c->lema, 'attr') ?>.">
    <meta property="og:url" content="<?= esc(base_url(), 'attr') ?>">
    <meta property="og:image" content="<?= base_url('img/og.jpg') ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('img/apple-touch-icon.png') ?>">
    <script type="application/ld+json"><?= json_encode([
        '@context'            => 'https://schema.org',
        '@type'               => 'Event',
        'name'                => $c->nombre . ' 2027 · ' . $c->edicion,
        'startDate'           => '2027-06-17',
        'endDate'             => '2027-06-19',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus'         => 'https://schema.org/EventScheduled',
        'location'            => ['@type' => 'Place', 'name' => $c->sede, 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Ciudad de Panamá', 'addressCountry' => 'PA']],
        'image'               => [base_url('img/og.jpg')],
        'organizer'           => ['@type' => 'Organization', 'name' => 'Sociedad Panameña de Pediatría'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&family=Ubuntu:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= is_file($css) ? filemtime($css) : 0 ?>">
    <noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>
</head>
<body>
<a href="#contenido" class="skip-link">Saltar al contenido</a>
<?= $this->include('Modules\Landing\Views\sections\navbar') ?>
<main id="contenido">
    <?= $this->include('Modules\Landing\Views\sections\galeria') ?>
    <?= $this->include('Modules\Landing\Views\sections\bienvenida') ?>
    <?= $this->include('Modules\Landing\Views\sections\hero') ?>
    <?= $this->include('Modules\Landing\Views\sections\tarifas') ?>
    <?= $this->include('Modules\Landing\Views\sections\registro') ?>
    <?= $this->include('Modules\Landing\Views\sections\internacionales') ?>
    <?= $this->include('Modules\Landing\Views\sections\aliados') ?>
    <?= $this->include('Modules\Landing\Views\sections\contacto') ?>
</main>
<?= $this->include('Modules\Landing\Views\sections\footer') ?>
<script type="application/json" id="datos-landing"><?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="<?= base_url('assets/js/landing.js') ?>?v=<?= is_file($js) ? filemtime($js) : 0 ?>" defer></script>
</body>
</html>

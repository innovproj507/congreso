<?php

/** @var \CodeIgniter\Router\RouteCollection $routes */
// API pública de inscripción (la usa el formulario de la landing)
$routes->group('api/eventos', ['namespace' => 'Modules\Eventos\Controllers'], static function ($routes) {
    $routes->post('(:segment)/inscripciones', 'InscripcionController::crear/$1');
});

// Avisos de Tesorería (servidor a servidor, firmados)
$routes->post('api/integracion/pagos', '\Modules\Eventos\Controllers\WebhookTesoreriaController::recibir');

// Panel interno
$routes->group('admin/inscripciones', ['namespace' => 'Modules\Eventos\Controllers', 'filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'AdminInscripcionController::index');
    $routes->get('exportar', 'AdminInscripcionController::exportar');
    $routes->post('(:num)/cancelar', 'AdminInscripcionController::cancelar/$1');
});

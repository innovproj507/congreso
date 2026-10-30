<?php

/** @var \CodeIgniter\Router\RouteCollection $routes */
$routes->group('', ['namespace' => 'Modules\Landing\Controllers'], static function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->post('contacto', 'Home::contacto');
});

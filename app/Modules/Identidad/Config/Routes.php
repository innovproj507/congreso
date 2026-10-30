<?php

/** @var \CodeIgniter\Router\RouteCollection $routes */
$routes->group('admin', ['namespace' => 'Modules\Identidad\Controllers'], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::entrar');
    $routes->post('logout', 'AuthController::salir', ['filter' => 'auth']);
    $routes->get('/', 'AuthController::inicio', ['filter' => 'auth']);
});

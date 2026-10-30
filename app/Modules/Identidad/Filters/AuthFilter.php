<?php

namespace Modules\Identidad\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

// Uso en rutas: ['filter' => 'auth'] (cualquier usuario) o ['filter' => 'auth:administracion,gerencia'] (solo esos roles).
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $u = session('usuario');
        if (! $u) {
            session()->set('despues_login', (string) current_url());

            return redirect()->to(site_url('admin/login'));
        }
        if ($arguments && ! in_array($u['rol'], $arguments, true)) {
            return service('response')->setStatusCode(403)->setBody(view('Modules\Identidad\Views\prohibido'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Las páginas internas no deben quedar en caché del navegador
        return $response->setHeader('Cache-Control', 'no-store, max-age=0');
    }
}

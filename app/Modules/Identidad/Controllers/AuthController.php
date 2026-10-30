<?php

namespace Modules\Identidad\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Identidad\Services\AuthService;

class AuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session('usuario')) {
            return redirect()->to(site_url('admin'));
        }

        return view('Modules\Identidad\Views\login', ['error' => session()->getFlashdata('error')]);
    }

    public function entrar(): RedirectResponse
    {
        // Máximo 10 intentos por minuto y por IP (además del bloqueo por cuenta)
        if (! service('throttler')->check('login_' . md5($this->request->getIPAddress()), 10, MINUTE)) {
            return redirect()->to(site_url('admin/login'))->with('error', 'Demasiados intentos. Espera un minuto.');
        }

        $r = (new AuthService())->intentar((string) $this->request->getPost('correo'), (string) $this->request->getPost('clave'));
        if (! $r['ok']) {
            return redirect()->to(site_url('admin/login'))->with('error', $r['error']);
        }

        $destino = session()->get('despues_login');
        session()->remove('despues_login');
        // Solo se redirige a rutas del propio sitio
        $propio = is_string($destino) && str_starts_with($destino, base_url());

        return redirect()->to($propio ? $destino : site_url('admin'));
    }

    public function salir(): RedirectResponse
    {
        (new AuthService())->salir();

        return redirect()->to(site_url('admin/login'));
    }

    public function inicio(): string
    {
        return view('Modules\Identidad\Views\inicio');
    }
}

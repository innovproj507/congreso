<?php

namespace Modules\Landing\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Eventos\Services\EventoService;
use Modules\Landing\Config\Congreso;
use Modules\Landing\Services\GaleriaService;

class Home extends BaseController
{
    public function index(): string
    {
        $c = config(Congreso::class);

        return view('Modules\Landing\Views\pagina', [
            'c'        => $c,
            'catalogo' => (new EventoService())->catalogo($c->slug),
            'galeria'  => (new GaleriaService())->obtener(),
        ]);
    }

    // Consultas del formulario de contacto: llegan por correo a la organización
    public function contacto(): ResponseInterface
    {
        $throttler = service('throttler');
        if (! $throttler->check('contacto_' . md5($this->request->getIPAddress()), 3, MINUTE)) {
            return $this->responder(['ok' => false, 'error' => 'Demasiados intentos. Espera un minuto.'], 429);
        }
        $b = $this->request->getJSON(true);
        if (! is_array($b) || ! empty($b['sitio_web'])) {
            return $this->responder(['ok' => ! empty($b['sitio_web'])], ! empty($b['sitio_web']) ? 200 : 400);
        }
        $nombre  = mb_substr(trim(strip_tags((string) ($b['nombre'] ?? ''))), 0, 100);
        $correo  = mb_substr(trim((string) ($b['correo'] ?? '')), 0, 150);
        $mensaje = mb_substr(trim(strip_tags((string) ($b['mensaje'] ?? ''))), 0, 2000);
        if ($nombre === '' || $mensaje === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->responder(['ok' => false, 'error' => 'Revisa nombre, correo y mensaje.'], 422);
        }

        $c = config(Congreso::class);
        try {
            $email = service('email');
            $email->clear();
            $email->setTo($c->correo);
            $email->setReplyTo($correo, $nombre);
            $email->setSubject('Consulta Congreso 2027 · ' . $nombre);
            $email->setMessage('<p><strong>' . esc($nombre) . '</strong> (' . esc($correo) . ')</p><p>' . nl2br(esc($mensaje)) . '</p>');
            if (! $email->send(false)) {
                log_message('error', 'Consulta de contacto no enviada: ' . strip_tags($email->printDebugger(['headers'])));

                return $this->responder(['ok' => false, 'error' => 'No pudimos enviar tu mensaje. Escríbenos a ' . $c->correo . '.'], 502);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Consulta de contacto falló: ' . $e->getMessage());

            return $this->responder(['ok' => false, 'error' => 'No pudimos enviar tu mensaje. Escríbenos a ' . $c->correo . '.'], 502);
        }

        return $this->responder(['ok' => true]);
    }

    private function responder(array $cuerpo, int $status = 200): ResponseInterface
    {
        $cuerpo['csrf'] = csrf_hash();

        return $this->response->setStatusCode($status)->setJSON($cuerpo);
    }
}

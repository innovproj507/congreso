<?php

namespace Modules\Eventos\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Eventos\Services\EventoService;
use Modules\Eventos\Services\NotificadorInscripcion;

// API pública de inscripción. La usa el formulario de la landing (y mañana el portal o una app).
class InscripcionController extends BaseController
{
    public function crear(string $slug): ResponseInterface
    {
        // Máximo 5 intentos por minuto y por IP
        $throttler = service('throttler');
        if (! $throttler->check('inscripcion_' . md5($this->request->getIPAddress()), 5, MINUTE)) {
            return $this->responder(['ok' => false, 'error' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.'], 429);
        }

        $b = $this->request->getJSON(true);
        if (! is_array($b)) {
            return $this->responder(['ok' => false, 'error' => 'Solicitud inválida.'], 400);
        }

        // Campo trampa: los humanos no lo ven; los bots lo llenan. Se responde "éxito" sin guardar nada.
        if (! empty($b['sitio_web'])) {
            return $this->responder(['ok' => true, 'codigo' => 'SPP27-XXXXXX', 'precio' => '']);
        }

        $limpio = static fn ($v, int $max) => is_string($v) ? mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($v))), 0, $max) : '';
        // «JUAN PÉREZ» o «juan pérez» se guardan como «Juan Pérez»; si ya trae mayúsculas y minúsculas mezcladas, se respeta («de la Cruz»)
        $nombrePropio = static function (string $t): string {
            return ($t !== '' && (mb_strtoupper($t) === $t || mb_strtolower($t) === $t)) ? mb_convert_case($t, MB_CASE_TITLE, 'UTF-8') : $t;
        };
        $datos  = [
            'categoria'   => $limpio($b['categoria'] ?? '', 120),
            'pago'        => $limpio($b['pago'] ?? '', 80),
            'nombre'      => $nombrePropio($limpio($b['nombre'] ?? '', 100)),
            'apellido'    => $nombrePropio($limpio($b['apellido'] ?? '', 100)),
            'documento'   => mb_strtoupper($limpio($b['documento'] ?? '', 40)),
            'pais'        => $nombrePropio($limpio($b['pais'] ?? '', 80)),
            'correo'      => mb_strtolower($limpio($b['correo'] ?? '', 150)),
            'telefono'    => $limpio($b['telefono'] ?? '', 40),
            'institucion' => $nombrePropio($limpio($b['institucion'] ?? '', 200)),
            'acepta'      => ($b['acepta'] ?? false) === true ? '1' : '',
        ];

        $v = service('validation');
        $v->setRules([
            'nombre'    => 'required|min_length[2]',
            'apellido'  => 'required|min_length[2]',
            'documento' => 'required|min_length[4]|regex_match[/^[A-Za-z0-9\-\. ]+$/]',
            'pais'      => 'required|min_length[2]',
            'correo'    => 'required|valid_email',
            'telefono'  => 'required|regex_match[/^(?=(?:\D*\d){7,})[0-9+\-\(\) ]{7,}$/]',
            'categoria' => 'required',
            'pago'      => 'required',
            'acepta'    => 'required',
        ], [
            'nombre'    => ['required' => 'Escribe tu nombre.', 'min_length' => 'Revisa tu nombre.'],
            'apellido'  => ['required' => 'Escribe tu apellido.', 'min_length' => 'Revisa tu apellido.'],
            'documento' => ['required' => 'Escribe tu cédula o pasaporte.', 'min_length' => 'Revisa tu cédula o pasaporte.', 'regex_match' => 'El documento solo admite letras, números y guiones.'],
            'pais'      => ['required' => 'Indica tu país de residencia.', 'min_length' => 'Revisa el país.'],
            'correo'    => ['required' => 'Escribe tu correo electrónico.', 'valid_email' => 'Revisa el correo electrónico.'],
            'telefono'  => ['required' => 'Escribe tu teléfono o WhatsApp.', 'regex_match' => 'Revisa el teléfono: usa solo números (con código de país si es del extranjero).'],
            'categoria' => ['required' => 'Elige una categoría.'],
            'pago'      => ['required' => 'Elige una forma de pago.'],
            'acepta'    => ['required' => 'Debes aceptar la autorización para continuar.'],
        ]);
        if (! $v->run($datos)) {
            return $this->responder(['ok' => false, 'error' => implode(' ', array_values($v->getErrors())), 'campos' => array_keys($v->getErrors())], 422);
        }

        $r = (new EventoService())->registrar($slug, $datos, $this->request->getIPAddress());
        if (! $r['ok']) {
            return $this->responder(['ok' => false, 'error' => $r['error']], $r['status']);
        }

        $servicio = new EventoService();
        if ((new NotificadorInscripcion())->enviar($r)) {
            $servicio->marcarCorreoEnviado((int) $r['inscripcion']['id']);
        }

        $ins = $r['inscripcion'];

        return $this->responder([
            'ok' => true, 'codigo' => $r['codigo'], 'precio' => $r['precio'], 'periodo' => $r['periodo'], 'reenvio' => ! empty($r['reenvio']),
            'nombre' => $ins['nombre'], 'categoria' => $ins['categoria'], 'pago' => $ins['forma_pago'], 'correo' => $ins['correo'],
            'instrucciones' => $r['instrucciones'] ?? '', 'contacto' => $r['evento']['correo_contacto'],
        ], ! empty($r['reenvio']) ? 200 : 201);
    }

    private function responder(array $cuerpo, int $status = 200): ResponseInterface
    {
        // El token CSRF se regenera en cada envío: se devuelve para que el formulario siga funcionando
        $cuerpo['csrf'] = csrf_hash();

        return $this->response->setStatusCode($status)->setJSON($cuerpo);
    }
}

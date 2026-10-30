<?php

namespace Modules\Eventos\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Eventos\Services\CroemPasarela;
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

        $b = $this->request->getPost();
        if (! is_array($b) || $b === []) {
            return $this->responder(['ok' => false, 'error' => 'Solicitud inválida.'], 400);
        }

        // Campo trampa: los humanos no lo ven; los bots lo llenan. Se responde "éxito" sin guardar nada.
        if (! empty($b['sitio_web'])) {
            return $this->responder(['ok' => true, 'codigo' => 'SPP27-XXXXXX', 'precio' => '']);
        }

        $limpio = static fn ($v, int $max) => is_string($v) ? mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags(mb_convert_encoding($v, 'UTF-8', 'UTF-8')))), 0, $max) : '';
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
            'referencia'  => $limpio($b['referencia_pago'] ?? '', 80),
            'acepta'      => ($b['acepta'] ?? '') === 'true' ? '1' : '',
        ];

        $v = service('validation');
        $v->setRules([
            'nombre'    => 'required|min_length[2]',
            'apellido'  => 'required|min_length[2]',
            'documento' => 'required|min_length[4]|regex_match[/^[A-Za-z0-9\-\. ]+$/]',
            'pais'      => 'required|min_length[2]',
            'correo'    => 'required|valid_email',
            'telefono'  => 'required|regex_match[/^(?=(?:\D*\d){7,})[0-9+\-\(\) ]{7,}$/]',
            'institucion' => 'required|min_length[2]',
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
            'institucion' => ['required' => 'Escribe tu institución o centro de trabajo.', 'min_length' => 'Revisa tu institución o centro de trabajo.'],
            'categoria' => ['required' => 'Elige una categoría.'],
            'pago'      => ['required' => 'Elige una forma de pago.'],
            'acepta'    => ['required' => 'Debes aceptar la autorización para continuar.'],
        ]);
        if (! $v->run($datos)) {
            return $this->responder(['ok' => false, 'error' => implode(' ', array_values($v->getErrors())), 'campos' => array_keys($v->getErrors())], 422);
        }

        // ACH y Yappy exigen comprobante: imagen o PDF de hasta 10 MB
        $archivo = $this->request->getFile('comprobante');
        $pide    = in_array($datos['pago'], (new EventoService())->catalogo($slug)['comprobante'] ?? [], true);
        $tarjeta = $datos['pago'] === 'Tarjeta de crédito';
        $token   = $limpio($b['account_token'] ?? '', 200);
        $cvv     = is_string($b['cvv'] ?? null) ? $b['cvv'] : '';
        if ($tarjeta) {
            if ($token === '') {
                return $this->responder(['ok' => false, 'error' => 'Valida tu tarjeta antes de enviar.', 'campos' => ['tarjeta']], 422);
            }
            if (! preg_match('/^[0-9]{3,4}$/', $cvv)) {
                return $this->responder(['ok' => false, 'error' => 'Escribe el CVV de tu tarjeta (3 o 4 dígitos).', 'campos' => ['cvv']], 422);
            }
        }
        if ($pide) {
            if ($datos['referencia'] === '') {
                return $this->responder(['ok' => false, 'error' => 'Escribe el número de referencia o confirmación de tu pago.', 'campos' => ['referencia_pago']], 422);
            }
            if (! $archivo || ! $archivo->isValid()) {
                return $this->responder(['ok' => false, 'error' => 'Adjunta el comprobante de tu pago.', 'campos' => ['comprobante']], 422);
            }
            if ($archivo->getSize() > 10 * 1024 * 1024 || ! in_array($archivo->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
                return $this->responder(['ok' => false, 'error' => 'El comprobante debe ser JPG, PNG, WebP o PDF de máximo 10 MB.', 'campos' => ['comprobante']], 422);
            }
        }

        $r = (new EventoService())->registrar($slug, $datos, $this->request->getIPAddress());
        if (! $r['ok']) {
            return $this->responder(['ok' => false, 'error' => $r['error']], $r['status']);
        }

        $servicio = new EventoService();
        $adjunto  = null;
        if ($pide && empty($r['reenvio'])) {
            // Se guarda fuera de public/ con un nombre que no depende del archivo original
            $ext    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'][$archivo->getMimeType()];
            $dir    = WRITEPATH . 'uploads/comprobantes';
            $nombre = $r['codigo'] . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $archivo->move($dir, $nombre);
            $servicio->guardarPago((int) $r['inscripcion']['id'], ['comprobante' => $nombre, 'referencia_pago' => $datos['referencia']]);
            $r['inscripcion']['referencia_pago'] = $datos['referencia'];
            $adjunto = $dir . '/' . $nombre;
        }

        // Tarjeta: se cobra ahora. Si el banco la rechaza se descarta la inscripción recién creada para que pueda reintentar.
        $pagado = false;
        if ($tarjeta && empty($r['reenvio'])) {
            $monto = (int) ($r['inscripcion']['monto_centavos'] ?? 0);
            $cobro = $monto > 0
                ? (new CroemPasarela())->cobrar($token, $cvv, $monto, 'REG-' . $r['codigo'])
                : ['ok' => false, 'mensaje' => 'Esta categoría no se puede pagar con tarjeta. Elige otro medio de pago.'];
            if (! $cobro['ok']) {
                $servicio->descartar((int) $r['inscripcion']['id']);

                return $this->responder(['ok' => false, 'error' => $cobro['mensaje'], 'campos' => ['tarjeta']], 422);
            }
            $servicio->marcarPagadaConTarjeta((int) $r['inscripcion']['id'], $cobro['transaccion']);
            $r['inscripcion']['estado'] = 'pagada';
            $r['instrucciones'] = 'Tu pago con tarjeta fue aprobado (transacción ' . $cobro['transaccion'] . '). No tienes que hacer nada más.';
            $pagado = true;
        }
        $r['pagado'] = $pagado;
        if ((new NotificadorInscripcion())->enviar($r, $adjunto)) {
            $servicio->marcarCorreoEnviado((int) $r['inscripcion']['id']);
        }

        $ins = $r['inscripcion'];

        return $this->responder([
            'ok' => true, 'codigo' => $r['codigo'], 'precio' => $r['precio'], 'periodo' => $r['periodo'], 'reenvio' => ! empty($r['reenvio']),
            'nombre' => $ins['nombre'], 'categoria' => $ins['categoria'], 'pago' => $ins['forma_pago'], 'correo' => $ins['correo'],
            'pagado' => $pagado, 'instrucciones' => $r['instrucciones'] ?? '', 'contacto' => $r['evento']['correo_contacto'],
        ], ! empty($r['reenvio']) ? 200 : 201);
    }

    private function responder(array $cuerpo, int $status = 200): ResponseInterface
    {
        // El token CSRF se regenera en cada envío: se devuelve para que el formulario siga funcionando
        $cuerpo['csrf'] = csrf_hash();

        return $this->response->setStatusCode($status)->setJSON($cuerpo);
    }
}

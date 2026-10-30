<?php

namespace Modules\Eventos\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Eventos\Services\EventoService;

// Receptor de avisos de Tesorería (Django): pago.confirmado y pago.anulado.
// Seguridad: firma HMAC-SHA256 con secreto compartido, marca de tiempo reciente y evento único (idempotente).
class WebhookTesoreriaController extends BaseController
{
    private const TOLERANCIA_SEG = 300;

    public function recibir(): ResponseInterface
    {
        $secreto = (string) env('tesoreria.webhookSecret');
        if ($secreto === '') {
            log_message('error', 'Webhook de Tesorería sin tesoreria.webhookSecret configurado.');

            return $this->responder(503, 'No configurado.');
        }

        $cuerpo = (string) $this->request->getBody();
        $ts     = (string) $this->request->getHeaderLine('X-Marca-Tiempo');
        $firma  = (string) $this->request->getHeaderLine('X-Firma');

        if (! ctype_digit($ts) || abs(time() - (int) $ts) > self::TOLERANCIA_SEG) {
            return $this->responder(401, 'Marca de tiempo inválida.');
        }
        $esperada = hash_hmac('sha256', $ts . '.' . $cuerpo, $secreto);
        if (! hash_equals($esperada, $firma)) {
            log_message('warning', 'Webhook de Tesorería con firma inválida desde ' . $this->request->getIPAddress());

            return $this->responder(401, 'Firma inválida.');
        }

        $evento = json_decode($cuerpo, true);
        if (! is_array($evento) || empty($evento['id']) || empty($evento['tipo']) || ! is_array($evento['datos'] ?? null)) {
            return $this->responder(400, 'Evento inválido.');
        }
        if (! preg_match('/^[0-9a-f\-]{36}$/i', (string) $evento['id'])) {
            return $this->responder(400, 'Identificador inválido.');
        }

        $db = db_connect();
        // Idempotencia: el mismo evento no se procesa dos veces (Tesorería reintenta si no recibe 2xx)
        $db->transStart();
        $ya = $db->query('SELECT id FROM eventos_recibidos WHERE id_evento = ? FOR UPDATE', [$evento['id']])->getRowArray();
        if ($ya) {
            $db->transComplete();

            return $this->responder(200, 'Ya procesado.');
        }

        $svc = new EventoService();
        $res = match ($evento['tipo']) {
            'pago.confirmado' => $svc->registrarPago($evento['datos']),
            'pago.anulado'    => $svc->revertirPago($evento['datos']),
            default           => 'tipo ignorado',
        };
        $db->table('eventos_recibidos')->insert([
            'id_evento' => $evento['id'], 'tipo' => mb_substr((string) $evento['tipo'], 0, 60),
            'resultado' => mb_substr((string) $res, 0, 120), 'recibido_en' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->responder(500, 'No se pudo registrar.');
        }

        return $this->responder(200, (string) $res);
    }

    private function responder(int $status, string $mensaje): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON(['ok' => $status < 300, 'mensaje' => $mensaje]);
    }
}

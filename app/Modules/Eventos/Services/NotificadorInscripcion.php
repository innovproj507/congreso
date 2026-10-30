<?php

namespace Modules\Eventos\Services;

// Correos de una inscripción: confirmación al participante y aviso a la organización.
// Si el correo falla, la inscripción ya está guardada; solo se registra en el log.
class NotificadorInscripcion
{
    public function enviar(array $r, ?string $comprobante = null): bool
    {
        $ins = $r['inscripcion'];
        $ev  = $r['evento'];
        $datos = [
            'ins' => $ins, 'ev' => $ev, 'precio' => $r['precio'], 'codigo' => $r['codigo'],
            'contacto' => $ev['correo_contacto'], 'pagado' => ! empty($r['pagado']), 'instrucciones' => $r['instrucciones'] ?? '',
        ];

        try {
            $email = service('email');

            // Primero el aviso a la organización: si el servidor de correo está caído, no se reintenta con el participante
            $aviso = $ev['correo_contacto'] ?: env('congreso.correoAvisos');
            if ($aviso) {
                $email->clear();
                $email->setTo($aviso);
                $email->setReplyTo($ins['correo']);
                $email->setSubject('Nueva inscripción ' . $r['codigo'] . ' · ' . $ins['nombre'] . ' ' . $ins['apellido']);
                $email->setMessage(view('Modules\Eventos\Views\correo\aviso', $datos));
                if ($comprobante && is_file($comprobante)) {
                    $email->attach($comprobante);
                }
                if (! $email->send(false)) {
                    log_message('error', 'Aviso de inscripción no enviado: ' . strip_tags($email->printDebugger(['headers'])));

                    return false;
                }
            }

            $email->clear();
            $email->setTo($ins['correo']);
            $email->setSubject('Recibimos tu solicitud de inscripción · ' . $ev['nombre'] . ' (' . $r['codigo'] . ')');
            $email->setMessage(view('Modules\Eventos\Views\correo\confirmacion', $datos));
            $ok = $email->send(false);
            if (! $ok) {
                log_message('error', 'Correo al participante no enviado: ' . strip_tags($email->printDebugger(['headers'])));
            }

            return $ok;
        } catch (\Throwable $e) {
            log_message('error', 'Correo de inscripción falló: ' . $e->getMessage());

            return false;
        }
    }
}

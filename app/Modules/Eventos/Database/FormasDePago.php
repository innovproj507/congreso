<?php

namespace Modules\Eventos\Database;

/**
 * Medios de pago del congreso (los mismos del sistema anterior: ACH, Yappy y tarjeta de crédito).
 * Una sola lista para el seeder (instalaciones nuevas) y la migración (bases existentes).
 * «{correo}» se reemplaza por el correo de contacto del evento.
 */
final class FormasDePago
{
    /** @return list<array{nombre:string,comprobante:bool,instrucciones:string}> */
    public static function lista(): array
    {
        return [
            ['nombre' => 'ACH / Transferencia', 'comprobante' => true, 'instrucciones' => 'Transfiere a Banco Nacional de Panamá, cuenta corriente 10000354366, a nombre de la Sociedad Panameña de Pediatría. Luego envía el comprobante a {correo} junto con tu código de inscripción.'],
            ['nombre' => 'Yappy', 'comprobante' => true, 'instrucciones' => 'Envía el pago por Yappy a @sociedadpanamenadepediatria y guarda el comprobante. Luego envíalo a {correo} junto con tu código de inscripción.'],
            ['nombre' => 'Tarjeta de crédito', 'comprobante' => false, 'instrucciones' => 'Te enviaremos a {correo} el enlace seguro para pagar con tu tarjeta de crédito o débito. Escríbenos con tu código de inscripción si no lo recibes en 24 horas.'],
        ];
    }

    /** Deja activos solo estos medios (y desactiva los anteriores sin borrarlos, por las inscripciones ya hechas). */
    public static function aplicar(\CodeIgniter\Database\BaseConnection $db): void
    {
        $t = $db->table('formas_pago');
        $db->table('formas_pago')->update(['activo' => 0]);
        foreach (self::lista() as $i => $f) {
            $fila = ['nombre' => $f['nombre'], 'instrucciones' => $f['instrucciones'], 'requiere_comprobante' => (int) $f['comprobante'], 'activo' => 1, 'orden' => $i];
            $existe = $db->table('formas_pago')->where('nombre', $f['nombre'])->get()->getRowArray();
            if ($existe) {
                $db->table('formas_pago')->where('id', $existe['id'])->update($fila);
            } else {
                $t->insert($fila);
                $t = $db->table('formas_pago');
            }
        }
    }
}

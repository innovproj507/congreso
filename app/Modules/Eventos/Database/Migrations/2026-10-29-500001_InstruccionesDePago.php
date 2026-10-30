<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;

// Cada forma de pago lleva sus instrucciones. Se muestran en el formulario, en la pantalla de confirmación y en el correo.
// «{correo}» se reemplaza por el correo de contacto del evento. Se editan en la tabla formas_pago sin tocar código.
class InstruccionesDePago extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('formas_pago', [
            'instrucciones' => ['type' => 'TEXT', 'null' => true, 'after' => 'nombre'],
        ]);

        $coordinar = 'Escríbenos a {correo} con tu código de inscripción para coordinar el pago.';
        $textos = [
            'Yappy'              => 'Envía el pago por Yappy a @sociedadpanamenadepediatria y guarda el comprobante. Luego envíalo a {correo} junto con tu código de inscripción.',
            'ACH Banco Nacional' => 'Transfiere a Banco Nacional de Panamá, cuenta corriente 10000354366, a nombre de la Sociedad Panameña de Pediatría. Luego envía el comprobante a {correo} junto con tu código de inscripción.',
            'Visa'               => $coordinar,
            'Mastercard'         => $coordinar,
            'Clave'              => $coordinar,
        ];
        foreach ($textos as $nombre => $texto) {
            $this->db->table('formas_pago')->where('nombre', $nombre)->update(['instrucciones' => $texto]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('formas_pago', 'instrucciones');
    }
}

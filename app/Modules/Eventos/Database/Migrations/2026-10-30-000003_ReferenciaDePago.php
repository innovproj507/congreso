<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;

// Referencia que escribe el inscrito en ACH/Yappy, o número de transacción del banco en pagos con tarjeta.
class ReferenciaDePago extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('inscripciones', [
            'referencia_pago' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'comprobante'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('inscripciones', 'referencia_pago');
    }
}

<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;

// ACH y Yappy exigen adjuntar el comprobante. El archivo se guarda fuera de public/ (writable/uploads/comprobantes)
// y aquí solo queda su nombre.
class ComprobanteDePago extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('formas_pago', [
            'requiere_comprobante' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'instrucciones'],
        ]);
        $this->forge->addColumn('inscripciones', [
            'comprobante' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'forma_pago'],
        ]);
        $this->db->table('formas_pago')->whereIn('nombre', ['ACH / Transferencia', 'Yappy'])->update(['requiere_comprobante' => 1]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('inscripciones', 'comprobante');
        $this->forge->dropColumn('formas_pago', 'requiere_comprobante');
    }
}

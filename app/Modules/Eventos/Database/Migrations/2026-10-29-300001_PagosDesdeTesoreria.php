<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;

// Tesorería (sistema aparte, Django) avisa los pagos por webhook firmado.
// Se guarda el folio del recibo en la inscripción y se registra cada aviso recibido para no procesarlo dos veces.
class PagosDesdeTesoreria extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('inscripciones', [
            'recibo_folio' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true, 'after' => 'estado'],
            'pagado_en'    => ['type' => 'DATETIME', 'null' => true, 'after' => 'recibo_folio'],
        ]);

        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'id_evento'   => ['type' => 'CHAR', 'constraint' => 36],
            'tipo'        => ['type' => 'VARCHAR', 'constraint' => 60],
            'resultado'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'recibido_en' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('id_evento');
        $this->forge->createTable('eventos_recibidos');
    }

    public function down(): void
    {
        $this->forge->dropTable('eventos_recibidos', true);
        $this->forge->dropColumn('inscripciones', ['recibo_folio', 'pagado_en']);
    }
}

<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;
use Modules\Eventos\Database\TarifasOficiales as Tarifario;

// Carga el tarifario oficial (Early Bird / Regular) en el evento del Congreso 2027.
// Las inscripciones ya guardadas conservan su categoría y monto (son una copia, no una referencia).
class TarifasOficiales extends Migration
{
    public function up(): void
    {
        $evento = $this->db->table('eventos')->where('slug', 'congreso-2027')->get()->getRowArray();
        if ($evento) {
            Tarifario::aplicar($this->db, (int) $evento['id']);
        }
    }

    public function down(): void
    {
        // No se revierte: las tarifas anteriores eran provisionales («por confirmar»).
    }
}

<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;
use Modules\Eventos\Database\FormasDePago;

// Medios de pago iguales a los del sistema anterior (ACH, Yappy) más tarjeta de crédito.
class FormasDePagoSpp extends Migration
{
    public function up(): void
    {
        FormasDePago::aplicar($this->db);
    }

    public function down(): void
    {
        $this->db->table('formas_pago')->whereIn('nombre', ['ACH / Transferencia', 'Tarjeta de crédito'])->update(['activo' => 0]);
    }
}

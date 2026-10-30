<?php

namespace Modules\Eventos\Database\Seeds;

use CodeIgniter\Database\Seeder;

// Evento inicial con el tarifario oficial.
class Congreso2027Seeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('eventos')->insert([
            'slug'            => 'congreso-2027',
            'nombre'          => 'LV Congreso Nacional de Pediatría',
            'edicion'         => 'Edición Esmeralda',
            'sede'            => 'Megapolis Convention Center',
            'fecha_inicio'    => '2027-06-17 09:00:00',
            'fecha_fin'       => '2027-06-19 16:00:00',
            'fin_preventa'    => '2027-03-15 23:59:59',
            'correo_contacto' => 'congresodepediatria@mestudiobtl.net',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $eventoId = $this->db->insertID();

        // Tarifario oficial (Early Bird y Regular): ver Database/TarifasOficiales.php
        \Modules\Eventos\Database\TarifasOficiales::aplicar($this->db, (int) $eventoId);
        foreach (['Yappy', 'Visa', 'Mastercard', 'Clave', 'ACH Banco Nacional'] as $i => $f) {
            $this->db->table('formas_pago')->insert(['nombre' => $f, 'orden' => $i]);
        }
    }
}

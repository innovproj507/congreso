<?php

namespace Modules\Eventos\Database;

/**
 * Tarifario oficial del LV Congreso Nacional de Pediatría 2027 (arte «Inscripciones / Becas»).
 * Una sola lista para el seeder (instalaciones nuevas) y la migración (bases existentes).
 * Precios en centavos. 0 = sin costo.
 */
final class TarifasOficiales
{
    /** @return list<array{categoria:string,preventa:int,regular:int}> */
    public static function lista(): array
    {
        return [
            ['categoria' => 'Socios activos',                                      'preventa' => 18500, 'regular' => 23700],
            ['categoria' => 'No socios',                                           'preventa' => 23700, 'regular' => 26300],
            ['categoria' => 'Médico general',                                      'preventa' => 21100, 'regular' => 23700],
            ['categoria' => 'Médicos residentes, internos y estudiantes',          'preventa' => 8000,  'regular' => 10600],
            ['categoria' => 'Enfermeras y profesionales de la salud',              'preventa' => 10600, 'regular' => 13200],
            ['categoria' => 'Socios eméritos',                                     'preventa' => 0,     'regular' => 0],
        ];
    }

    /** Reemplaza las tarifas del evento por las oficiales. Devuelve cuántas filas insertó. */
    public static function aplicar(\CodeIgniter\Database\BaseConnection $db, int $eventoId): int
    {
        $now = date('Y-m-d H:i:s');
        $db->table('tarifas')->where('evento_id', $eventoId)->delete();
        $n = 0;
        foreach (self::lista() as $orden => $t) {
            foreach (['preventa', 'regular'] as $periodo) {
                $db->table('tarifas')->insert([
                    'evento_id' => $eventoId, 'periodo' => $periodo, 'categoria' => $t['categoria'],
                    'precio_centavos' => $t[$periodo], 'orden' => $orden, 'activo' => 1, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $n++;
            }
        }

        return $n;
    }
}

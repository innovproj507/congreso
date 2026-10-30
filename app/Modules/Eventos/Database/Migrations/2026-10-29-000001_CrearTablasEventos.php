<?php

namespace Modules\Eventos\Database\Migrations;

use CodeIgniter\Database\Migration;

// Módulo Eventos: catálogo, tarifas con vigencia e inscripciones.
// Sin claves foráneas hacia otros módulos: persona_id guardará el identificador que asigne Tesorería.
class CrearTablasEventos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug'                   => ['type' => 'VARCHAR', 'constraint' => 80],
            'nombre'                 => ['type' => 'VARCHAR', 'constraint' => 200],
            'edicion'                => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'sede'                   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'fecha_inicio'           => ['type' => 'DATETIME'],
            'fecha_fin'              => ['type' => 'DATETIME'],
            'fin_preventa'           => ['type' => 'DATETIME', 'null' => true],
            'correo_contacto'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'cupo_total'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'inscripciones_abiertas' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('eventos');

        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'evento_id'       => ['type' => 'INT', 'unsigned' => true],
            'periodo'         => ['type' => 'ENUM', 'constraint' => ['preventa', 'regular']],
            'categoria'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'precio_centavos' => ['type' => 'INT', 'unsigned' => true, 'null' => true], // NULL = por confirmar
            'orden'           => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'activo'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['evento_id', 'periodo', 'categoria']);
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tarifas');

        $this->forge->addField([
            'id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 80],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'orden'  => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre');
        $this->forge->createTable('formas_pago');

        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'codigo'             => ['type' => 'VARCHAR', 'constraint' => 20],
            'evento_id'          => ['type' => 'INT', 'unsigned' => true],
            'tarifa_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'persona_id'         => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true], // lo asignará Tesorería
            'periodo'            => ['type' => 'ENUM', 'constraint' => ['preventa', 'regular']],
            'categoria'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'monto_centavos'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nombre'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'apellido'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'documento'          => ['type' => 'VARCHAR', 'constraint' => 40],
            'pais'               => ['type' => 'VARCHAR', 'constraint' => 80],
            'correo'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'telefono'           => ['type' => 'VARCHAR', 'constraint' => 40],
            'institucion'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'forma_pago'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'estado'             => ['type' => 'ENUM', 'constraint' => ['pendiente', 'pagada', 'cancelada'], 'default' => 'pendiente'],
            'canal'              => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'web'],
            'acepto_terminos_at' => ['type' => 'DATETIME', 'null' => true],
            'ip'                 => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'correo_enviado_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->addUniqueKey(['evento_id', 'documento']);
        $this->forge->addKey('correo');
        $this->forge->addKey(['evento_id', 'estado']);
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('inscripciones');
    }

    public function down(): void
    {
        $this->forge->dropTable('inscripciones', true);
        $this->forge->dropTable('formas_pago', true);
        $this->forge->dropTable('tarifas', true);
        $this->forge->dropTable('eventos', true);
    }
}

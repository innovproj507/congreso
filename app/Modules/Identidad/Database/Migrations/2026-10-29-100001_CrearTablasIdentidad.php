<?php

namespace Modules\Identidad\Database\Migrations;

use CodeIgniter\Database\Migration;

// M1 Identidad: usuarios internos (administración, gerencia, operador) y auditoría de solo inserción.
class CrearTablasIdentidad extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'             => ['type' => 'VARCHAR', 'constraint' => 120],
            'correo'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'password_hash'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'rol'                => ['type' => 'ENUM', 'constraint' => ['administracion', 'gerencia', 'operador']],
            'activo'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'intentos_fallidos'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'bloqueado_hasta'    => ['type' => 'DATETIME', 'null' => true],
            'ultimo_acceso'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('correo');
        $this->forge->createTable('usuarios');

        // Solo se inserta: en producción el usuario de la aplicación no debe tener UPDATE ni DELETE aquí.
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'usuario_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'accion'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'entidad'     => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'entidad_id'  => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'detalle'     => ['type' => 'TEXT', 'null' => true],
            'ip'          => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['entidad', 'entidad_id']);
        $this->forge->addKey('usuario_id');
        $this->forge->createTable('auditoria');
    }

    public function down(): void
    {
        $this->forge->dropTable('auditoria', true);
        $this->forge->dropTable('usuarios', true);
    }
}

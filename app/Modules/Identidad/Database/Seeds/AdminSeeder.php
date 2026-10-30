<?php

namespace Modules\Identidad\Database\Seeds;

use CodeIgniter\Database\Seeder;

// Crea el primer usuario de administración con los datos de .env (admin.correo, admin.nombre, admin.password).
// La contraseña nunca se guarda en el código: solo vive en .env, que no se sube a git.
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $correo = env('admin.correo');
        $clave  = env('admin.password');
        if (! $correo || ! $clave) {
            echo "Define admin.correo y admin.password en .env antes de ejecutar este seeder.\n";

            return;
        }
        if ($this->db->table('usuarios')->where('correo', mb_strtolower($correo))->countAllResults() > 0) {
            echo "El usuario ya existe.\n";

            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('usuarios')->insert([
            'nombre' => env('admin.nombre', 'Administración'), 'correo' => mb_strtolower($correo),
            'password_hash' => password_hash($clave, PASSWORD_DEFAULT), 'rol' => 'administracion',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        echo "Usuario de administración creado: {$correo}\n";
    }
}

<?php

namespace Modules\Identidad\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nombre', 'correo', 'password_hash', 'rol', 'activo', 'intentos_fallidos', 'bloqueado_hasta', 'ultimo_acceso'];
}

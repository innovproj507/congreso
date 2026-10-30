<?php

namespace Modules\Eventos\Models;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table         = 'eventos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'slug', 'nombre', 'edicion', 'sede', 'fecha_inicio', 'fecha_fin', 'fin_preventa',
        'correo_contacto', 'cupo_total', 'inscripciones_abiertas',
    ];
}

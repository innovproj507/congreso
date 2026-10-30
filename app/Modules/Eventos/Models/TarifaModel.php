<?php

namespace Modules\Eventos\Models;

use CodeIgniter\Model;

class TarifaModel extends Model
{
    protected $table         = 'tarifas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['evento_id', 'periodo', 'categoria', 'precio_centavos', 'orden', 'activo'];
}

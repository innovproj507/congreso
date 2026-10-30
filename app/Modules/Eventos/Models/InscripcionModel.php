<?php

namespace Modules\Eventos\Models;

use CodeIgniter\Model;

class InscripcionModel extends Model
{
    protected $table         = 'inscripciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'codigo', 'evento_id', 'tarifa_id', 'persona_id', 'periodo', 'categoria', 'monto_centavos',
        'nombre', 'apellido', 'documento', 'pais', 'correo', 'telefono', 'institucion', 'forma_pago',
        'estado', 'recibo_folio', 'pagado_en', 'canal', 'acepto_terminos_at', 'ip', 'correo_enviado_at',
    ];
}

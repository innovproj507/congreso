<?php

namespace Modules\Eventos\Services;

use DateTimeImmutable;
use DateTimeZone;
use Modules\Eventos\Models\EventoModel;
use Modules\Eventos\Models\InscripcionModel;
use Modules\Eventos\Models\TarifaModel;

// Punto de entrada del módulo Eventos. Otros módulos (Landing, luego Tesorería/Checkout)
// usan esta clase y no tocan sus tablas ni modelos directamente.
class EventoService
{
    private const TZ = 'America/Panama';

    public function __construct(
        private ?EventoModel $eventos = null,
        private ?TarifaModel $tarifas = null,
        private ?InscripcionModel $inscripciones = null,
    ) {
        $this->eventos       ??= new EventoModel();
        $this->tarifas       ??= new TarifaModel();
        $this->inscripciones ??= new InscripcionModel();
    }

    public function evento(string $slug): ?array
    {
        return $this->eventos->where('slug', $slug)->first();
    }

    public static function formatoPrecio(?int $centavos): string
    {
        if ($centavos === null) {
            return 'Por confirmar';
        }
        if ($centavos === 0) {
            return 'Sin costo';
        }
        $d = $centavos / 100;

        return '$' . ($centavos % 100 === 0 ? number_format($d, 0) : number_format($d, 2));
    }

    // La preventa vence en fin_preventa (hora de Panamá); después aplica la tarifa regular.
    public function periodoVigente(array $evento, ?DateTimeImmutable $ahora = null): string
    {
        $tz    = new DateTimeZone(self::TZ);
        $ahora = ($ahora ?? new DateTimeImmutable('now', $tz))->setTimezone($tz);
        if (empty($evento['fin_preventa'])) {
            return 'regular';
        }

        return $ahora <= new DateTimeImmutable($evento['fin_preventa'], $tz) ? 'preventa' : 'regular';
    }

    /** Tarifas listas para mostrar, agrupadas por periodo. */
    public function catalogo(string $slug): ?array
    {
        $ev = $this->evento($slug);
        if (! $ev) {
            return null;
        }
        $filas = $this->tarifas->where('evento_id', $ev['id'])->where('activo', 1)->orderBy('orden')->findAll();
        $por   = ['preventa' => [], 'regular' => []];
        foreach ($filas as $f) {
            $por[$f['periodo']][] = [
                'id'        => (int) $f['id'],
                'categoria' => $f['categoria'],
                'centavos'  => $f['precio_centavos'] === null ? null : (int) $f['precio_centavos'],
                'precio'    => self::formatoPrecio($f['precio_centavos'] === null ? null : (int) $f['precio_centavos']),
            ];
        }
        $formas = db_connect()->table('formas_pago')->where('activo', 1)->orderBy('orden')->get()->getResultArray();

        $instrucciones = [];
        foreach ($formas as $f) {
            $instrucciones[$f['nombre']] = str_replace('{correo}', (string) $ev['correo_contacto'], (string) ($f['instrucciones'] ?? ''));
        }

        return [
            'evento'        => $ev,
            'vigente'       => $this->periodoVigente($ev),
            'tarifas'       => $por,
            'formas'        => array_column($formas, 'nombre'),
            'instrucciones' => $instrucciones,
            'comprobante'   => array_column(array_filter($formas, static fn ($f) => (int) ($f['requiere_comprobante'] ?? 0) === 1), 'nombre'),
        ];
    }

    /**
     * Registra una inscripción en estado "pendiente".
     *
     * @return array{ok:bool,status:int,error?:string,codigo?:string,precio?:string,periodo?:string,inscripcion?:array}
     */
    public function registrar(string $slug, array $d, ?string $ip = null): array
    {
        $cat = $this->catalogo($slug);
        if (! $cat) {
            return $this->fallo(404, 'Evento no encontrado.');
        }
        $ev = $cat['evento'];
        if (! (int) $ev['inscripciones_abiertas']) {
            return $this->fallo(409, 'Las inscripciones no están abiertas por el momento.');
        }

        $periodo = $cat['vigente'];
        $tarifa  = null;
        foreach ($cat['tarifas'][$periodo] as $t) {
            if ($t['categoria'] === ($d['categoria'] ?? '')) {
                $tarifa = $t;
            }
        }
        if (! $tarifa || ! in_array($d['pago'] ?? '', $cat['formas'], true)) {
            return $this->fallo(422, 'Categoría o forma de pago inválidas.');
        }

        $db = db_connect();
        $db->transStart();

        if ($ev['cupo_total'] !== null) {
            $n = $this->inscripciones->where('evento_id', $ev['id'])->where('estado !=', 'cancelada')->countAllResults();
            if ($n >= (int) $ev['cupo_total']) {
                $db->transComplete();

                return $this->fallo(409, 'Se agotaron los cupos disponibles.');
            }
        }

        $existe = $this->inscripciones->where('evento_id', $ev['id'])->where('documento', $d['documento'])->first();
        if ($existe) {
            $db->transComplete();

            // Mismo documento y mismo correo = la persona repite el envío (se cortó la conexión, volvió a pulsar...):
            // se le devuelve su inscripción en lugar de un error, sin crear otra.
            if ($existe['estado'] !== 'cancelada' && mb_strtolower($existe['correo']) === mb_strtolower($d['correo'])) {
                return [
                    'ok' => true, 'status' => 200, 'reenvio' => true, 'codigo' => $existe['codigo'],
                    'precio' => self::formatoPrecio($existe['monto_centavos'] === null ? null : (int) $existe['monto_centavos']),
                    'periodo' => $existe['periodo'], 'inscripcion' => $existe, 'evento' => $ev,
                    'instrucciones' => $cat['instrucciones'][$existe['forma_pago']] ?? '',
                ];
            }

            return $this->fallo(409, 'Ya existe una inscripción con ese documento. Si es la tuya, usa el mismo correo con el que te inscribiste; si necesitas cambiarla, escríbenos a ' . $ev['correo_contacto'] . '.');
        }

        $codigo = $this->generarCodigo();
        $fila   = [
            'codigo'             => $codigo,
            'evento_id'          => $ev['id'],
            'tarifa_id'          => $tarifa['id'],
            'periodo'            => $periodo,
            'categoria'          => $tarifa['categoria'],
            'monto_centavos'     => $tarifa['centavos'],
            'nombre'             => $d['nombre'],
            'apellido'           => $d['apellido'],
            'documento'          => $d['documento'],
            'pais'               => $d['pais'],
            'correo'             => $d['correo'],
            'telefono'           => $d['telefono'],
            'institucion'        => $d['institucion'] !== '' ? $d['institucion'] : null,
            'forma_pago'         => $d['pago'],
            'comprobante'        => null,
            'estado'             => 'pendiente',
            'canal'              => 'web',
            'acepto_terminos_at' => date('Y-m-d H:i:s'),
            'ip'                 => $ip,
        ];
        try {
            $this->inscripciones->insert($fila);
        } catch (\Throwable $e) {
            // Dos envíos simultáneos del mismo documento: gana el primero (restricción única en la base)
            $db->transRollback();
            log_message('warning', 'Inscripción duplicada simultánea: ' . $e->getMessage());

            return $this->fallo(409, 'Tu inscripción ya se está procesando. Revisa tu correo en unos minutos.');
        }
        $fila['id'] = $this->inscripciones->getInsertID();
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fallo(500, 'No se pudo guardar la inscripción. Intenta de nuevo.');
        }

        return ['ok' => true, 'status' => 201, 'codigo' => $codigo, 'precio' => $tarifa['precio'], 'periodo' => $periodo, 'inscripcion' => $fila, 'evento' => $ev, 'instrucciones' => $cat['instrucciones'][$d['pago']] ?? ''];
    }

    /** Guarda lo que el inscrito aportó para su pago (comprobante y referencia). */
    public function guardarPago(int $inscripcionId, array $campos): void
    {
        $this->inscripciones->update($inscripcionId, $campos);
    }

    /** Pago con tarjeta aprobado por el banco: la inscripción queda pagada de inmediato. */
    public function marcarPagadaConTarjeta(int $inscripcionId, string $transaccion): void
    {
        $this->inscripciones->update($inscripcionId, ['estado' => 'pagada', 'pagado_en' => date('Y-m-d H:i:s'), 'referencia_pago' => $transaccion]);
    }

    /** Borra una inscripción recién creada cuyo cobro con tarjeta fue rechazado, para que la persona pueda reintentar. */
    public function descartar(int $inscripcionId): void
    {
        $this->inscripciones->delete($inscripcionId);
    }

    public function marcarCorreoEnviado(int $inscripcionId): void
    {
        $this->inscripciones->update($inscripcionId, ['correo_enviado_at' => date('Y-m-d H:i:s')]);
    }

    public function inscripcion(int $id): ?array
    {
        return $this->inscripciones->find($id);
    }

    /** Lista para el panel. $estado: '' (todos), pendiente, pagada o cancelada. */
    public function listarInscripciones(string $q = '', string $estado = '', int $limite = 500): array
    {
        $m = $this->inscripciones;
        if ($estado !== '') {
            $m = $m->where('estado', $estado);
        }
        if ($q !== '') {
            $m = $m->groupStart()->like('codigo', $q)->orLike('nombre', $q)->orLike('apellido', $q)
                ->orLike('documento', $q)->orLike('correo', $q)->groupEnd();
        }

        return $m->orderBy('id', 'DESC')->findAll($limite);
    }

    /** Totales por estado y por categoría para el encabezado del panel. */
    public function resumen(): array
    {
        $db   = db_connect();
        $est  = $db->query("SELECT estado, COUNT(*) n FROM inscripciones GROUP BY estado")->getResultArray();
        $cat  = $db->query("SELECT categoria, COUNT(*) n, SUM(estado = 'pagada') pagadas FROM inscripciones WHERE estado <> 'cancelada' GROUP BY categoria ORDER BY n DESC")->getResultArray();

        return ['estados' => array_column($est, 'n', 'estado'), 'categorias' => $cat];
    }

    /** Solo se cancelan inscripciones pendientes: una pagada requiere anular primero su recibo. */
    public function cancelar(int $id): bool
    {
        $i = $this->inscripciones->find($id);
        if (! $i || $i['estado'] !== 'pendiente') {
            return false;
        }

        return (bool) $this->inscripciones->update($id, ['estado' => 'cancelada']);
    }

    /** Busca por código (SPP27-XXXXXX, lo que envía Tesorería) o por id numérico. */
    private function porReferencia(string $ref): ?array
    {
        $ref = trim($ref);
        if ($ref === '') {
            return null;
        }

        return ctype_digit($ref) ? $this->inscripciones->find((int) $ref) : $this->inscripciones->where('codigo', strtoupper($ref))->first();
    }

    // Aviso de Tesorería pago.confirmado: la inscripción pasa a pagada y guarda el folio del recibo.
    public function registrarPago(array $p): string
    {
        if (($p['origen_tipo'] ?? null) !== 'inscripcion') {
            return 'sin inscripción asociada';
        }
        $i = $this->porReferencia((string) ($p['origen_id'] ?? ''));
        if (! $i) {
            log_message('warning', 'Pago de Tesorería para una inscripción inexistente: ' . ($p['origen_id'] ?? ''));

            return 'inscripción no encontrada';
        }
        if ($i['estado'] === 'cancelada') {
            log_message('warning', 'Pago recibido para una inscripción cancelada: ' . $i['codigo']);
        }
        $this->inscripciones->update($i['id'], [
            'estado' => 'pagada', 'recibo_folio' => $p['folio'] ?? null, 'pagado_en' => date('Y-m-d H:i:s'),
            'persona_id' => $p['persona_id'] ?? $i['persona_id'],
        ]);

        return 'pagada ' . $i['codigo'];
    }

    // Aviso de Tesorería pago.anulado: la inscripción vuelve a pendiente.
    public function revertirPago(array $p): string
    {
        if (($p['origen_tipo'] ?? null) !== 'inscripcion') {
            return 'sin inscripción asociada';
        }
        $i = $this->porReferencia((string) ($p['origen_id'] ?? ''));
        if (! $i) {
            return 'inscripción no encontrada';
        }
        // Solo se revierte si el recibo anulado es el que estaba registrado (un aviso viejo no pisa un pago nuevo)
        if (! empty($p['folio']) && $i['recibo_folio'] !== null && $i['recibo_folio'] !== $p['folio']) {
            return 'recibo distinto, sin cambios';
        }
        $this->inscripciones->update($i['id'], ['estado' => 'pendiente', 'recibo_folio' => null, 'pagado_en' => null]);

        return 'pendiente ' . $i['codigo'];
    }

    private function generarCodigo(): string
    {
        $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $c = 'SPP27-';
            for ($i = 0; $i < 6; $i++) {
                $c .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
        } while ($this->inscripciones->where('codigo', $c)->countAllResults() > 0);

        return $c;
    }

    private function fallo(int $status, string $msg): array
    {
        return ['ok' => false, 'status' => $status, 'error' => $msg];
    }
}

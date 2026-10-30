<?php

namespace Modules\Eventos\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Eventos\Services\EventoService;
use Modules\Identidad\Services\Auditoria;

// Panel interno: lista de inscritos (lo principal de Eventos) y exportación.
class AdminInscripcionController extends BaseController
{
    private function filtros(): array
    {
        $q      = mb_substr(trim((string) $this->request->getGet('q')), 0, 80);
        $estado = in_array($this->request->getGet('estado'), ['pendiente', 'pagada', 'cancelada'], true) ? (string) $this->request->getGet('estado') : '';

        return [$q, $estado];
    }

    public function index(): string
    {
        [$q, $estado] = $this->filtros();
        $svc          = new EventoService();

        return view('Modules\Eventos\Views\admin\lista', [
            'inscritos' => $svc->listarInscripciones($q, $estado), 'resumen' => $svc->resumen(), 'q' => $q, 'estado' => $estado,
        ]);
    }

    public function cancelar(int $id): RedirectResponse
    {
        $ok = (new EventoService())->cancelar($id);
        if ($ok) {
            Auditoria::registrar('inscripcion.cancelada', 'inscripcion', $id);
        }

        return redirect()->back()->with($ok ? 'ok' : 'error', $ok ? 'Inscripción cancelada.' : 'Solo se pueden cancelar inscripciones pendientes (sin recibo vigente).');
    }

    public function exportar(): ResponseInterface
    {
        [$q, $estado] = $this->filtros();
        $filas        = (new EventoService())->listarInscripciones($q, $estado, 5000);
        Auditoria::registrar('inscripciones.exportadas', 'inscripcion', null, ['filtro' => $q, 'estado' => $estado, 'filas' => count($filas)]);

        // Evita que Excel ejecute fórmulas escritas por el visitante (inyección de CSV)
        $seguro = static fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : (string) $v;
        $h      = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF"); // UTF-8 con BOM para que Excel respete las tildes
        fputcsv($h, ['Código', 'Fecha', 'Estado', 'Nombre', 'Apellido', 'Documento', 'País', 'Correo', 'Teléfono', 'Institución', 'Categoría', 'Periodo', 'Monto (USD)', 'Forma de pago'], ';');
        foreach ($filas as $f) {
            fputcsv($h, array_map($seguro, [
                $f['codigo'], $f['created_at'], $f['estado'], $f['nombre'], $f['apellido'], $f['documento'], $f['pais'], $f['correo'],
                $f['telefono'], $f['institucion'], $f['categoria'], $f['periodo'],
                $f['monto_centavos'] === null ? '' : number_format($f['monto_centavos'] / 100, 2, '.', ''), $f['forma_pago'],
            ]), ';');
        }
        rewind($h);
        $csv = stream_get_contents($h);
        fclose($h);

        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="inscritos-' . date('Ymd-His') . '.csv"')->setBody($csv);
    }
}

<?= $this->extend('Modules\Identidad\Views\admin_layout') ?>
<?php
use Modules\Eventos\Services\EventoService;

$titulo = 'Inscritos';
$e      = $resumen['estados'];
$tot    = array_sum($e);
$panel  = rtrim((string) env('tesoreria.panelUrl'), '/');
// Enlace al panel de Tesorería (sistema aparte) con los datos del inscrito precargados
$urlPago = static function (array $i) use ($panel): string {
    $q = [
        'documento' => $i['documento'], 'nombre' => $i['nombre'], 'apellido' => $i['apellido'], 'correo' => $i['correo'],
        'telefono' => $i['telefono'], 'pais' => $i['pais'], 'institucion' => (string) $i['institucion'], 'forma_pago' => $i['forma_pago'],
        'concepto' => 'Inscripción LV Congreso Nacional de Pediatría 2027 · ' . $i['categoria'] . ' (' . $i['codigo'] . ')',
        'origen_id' => $i['codigo'],
    ];
    if ($i['monto_centavos'] !== null) {
        $q['monto'] = number_format($i['monto_centavos'] / 100, 2, '.', '');
    }

    return $panel . '/tesoreria/recibo/add/?' . http_build_query($q);
};
$badge  = ['pendiente' => 'bg-amber-50 text-amber-800', 'pagada' => 'bg-esm-100 text-esm-800', 'cancelada' => 'bg-gray-100 text-gray-600'];
?>
<?= $this->section('contenido') ?>
<div class="flex flex-wrap items-end justify-between gap-4">
    <h1 class="font-display text-3xl font-medium text-esm-900">Inscritos</h1>
    <a href="<?= site_url('admin/inscripciones/exportar?' . http_build_query(['q' => $q, 'estado' => $estado])) ?>" class="rounded-full border border-esm-800 px-6 py-3 font-semibold text-esm-800 hover:bg-white">Exportar a Excel (CSV)</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ([['Total', $tot, 'text-esm-900'], ['Pendientes de pago', $e['pendiente'] ?? 0, 'text-amber-700'], ['Pagadas', $e['pagada'] ?? 0, 'text-esm-800'], ['Canceladas', $e['cancelada'] ?? 0, 'text-gray-500']] as [$t, $n, $c]) : ?>
        <div class="rounded-[30px] bg-white p-6 shadow-sm"><p class="text-sm text-ink/60"><?= $t ?></p><p class="font-display mt-1 text-4xl font-bold <?= $c ?>"><?= (int) $n ?></p></div>
    <?php endforeach; ?>
</div>

<?php if ($resumen['categorias']) : ?>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach ($resumen['categorias'] as $c) : ?>
            <span class="rounded-full bg-white px-4 py-1.5 text-sm shadow-sm"><?= esc($c['categoria']) ?>: <strong><?= (int) $c['n'] ?></strong> <span class="text-ink/50">(<?= (int) $c['pagadas'] ?> pagadas)</span></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="get" class="mt-6 flex flex-wrap gap-3">
    <input name="q" value="<?= esc($q, 'attr') ?>" placeholder="Código, nombre, documento o correo" aria-label="Buscar" class="w-full max-w-sm rounded-full border border-esm-100 bg-white px-5 py-2.5 outline-none focus:border-esm-700">
    <select name="estado" aria-label="Estado" class="rounded-full border border-esm-100 bg-white px-5 py-2.5">
        <option value="">Todos</option>
        <?php foreach (['pendiente' => 'Pendientes', 'pagada' => 'Pagadas', 'cancelada' => 'Canceladas'] as $k => $t) : ?><option value="<?= $k ?>" <?= $estado === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
    </select>
    <button class="rounded-full bg-esm-900 px-6 py-2.5 font-semibold text-white">Buscar</button>
</form>

<div class="mt-6 overflow-x-auto rounded-[30px] bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-esm-50 text-esm-900"><tr>
            <th class="px-5 py-3">Código</th><th class="px-5 py-3">Participante</th><th class="px-5 py-3">Categoría</th>
            <th class="px-5 py-3">Pago</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Acciones</th>
        </tr></thead>
        <tbody>
        <?php foreach ($inscritos as $i) : ?>
            <tr class="border-t border-esm-100 align-top">
                <td class="px-5 py-3 font-semibold"><?= esc($i['codigo']) ?><span class="block text-xs font-normal text-ink/60"><?= esc(date('d/m/Y H:i', strtotime($i['created_at']))) ?></span></td>
                <td class="px-5 py-3"><?= esc($i['nombre'] . ' ' . $i['apellido']) ?>
                    <span class="block text-xs text-ink/60"><?= esc($i['documento']) ?> · <?= esc($i['pais']) ?></span>
                    <span class="block text-xs text-ink/60"><?= esc($i['correo']) ?> · <?= esc($i['telefono']) ?></span></td>
                <td class="px-5 py-3"><?= esc($i['categoria']) ?><span class="block text-xs text-ink/60"><?= esc(EventoService::formatoPrecio($i['monto_centavos'] === null ? null : (int) $i['monto_centavos'])) ?> · <?= esc($i['periodo']) ?></span></td>
                <td class="px-5 py-3"><?= esc($i['forma_pago']) ?></td>
                <td class="px-5 py-3"><span class="rounded-full px-3 py-1 text-xs font-bold <?= $badge[$i['estado']] ?>"><?= ucfirst($i['estado']) ?></span></td>
                <td class="px-5 py-3 text-right">
                    <?php if ($i['estado'] === 'pendiente') : ?>
                        <a href="<?= esc($urlPago($i), 'attr') ?>" target="_blank" rel="noopener" class="inline-block rounded-full bg-esm-800 px-4 py-2 text-xs font-semibold text-white hover:bg-esm-700">Registrar pago</a>
                        <form method="post" action="<?= site_url('admin/inscripciones/' . $i['id'] . '/cancelar') ?>" class="mt-1 inline" onsubmit="return confirm('¿Cancelar esta inscripción?')">
                            <?= csrf_field() ?><button class="rounded-full px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Cancelar</button></form>
                    <?php elseif ($i['estado'] === 'pagada') : ?>
                        <a href="<?= esc($panel . '/tesoreria/recibo/?q=' . urlencode((string) $i['recibo_folio']), 'attr') ?>" target="_blank" rel="noopener" class="text-xs font-semibold text-esm-700 underline"><?= esc($i['recibo_folio'] ?: 'Ver recibo') ?></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (! $inscritos) : ?><tr><td colspan="6" class="px-5 py-10 text-center text-ink/60">No hay inscripciones con ese filtro.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>

<?php /** @var array $ins @var array $ev @var string $precio @var string $codigo */ ?>
<!doctype html>
<html lang="es"><body style="font-family:Arial,Helvetica,sans-serif;color:#12294a">
<h3 style="margin:0 0 12px">Nueva inscripción <?= esc($codigo) ?></h3>
<table cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse">
<?php foreach ([
    'Nombre'       => $ins['nombre'] . ' ' . $ins['apellido'],
    'Documento'    => $ins['documento'],
    'País'         => $ins['pais'],
    'Correo'       => $ins['correo'],
    'Teléfono'     => $ins['telefono'],
    'Institución'  => $ins['institucion'] ?? '—',
    'Categoría'    => $ins['categoria'] . ' (' . $ins['periodo'] . ')',
    'Monto'        => $precio,
    'Forma de pago' => $ins['forma_pago'],
] as $k => $v) : ?>
<tr><td style="border-bottom:1px solid #dbeaf7;color:#5a6b85"><?= esc($k) ?></td><td style="border-bottom:1px solid #dbeaf7"><strong><?= esc($v) ?></strong></td></tr>
<?php endforeach; ?>
</table>
</body></html>

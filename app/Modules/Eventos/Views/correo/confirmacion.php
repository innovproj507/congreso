<?php /** @var array $ins @var array $ev @var string $precio @var string $codigo @var string $contacto @var string $instrucciones */ ?>
<!doctype html>
<html lang="es"><body style="margin:0;background:#eef6fc;font-family:Arial,Helvetica,sans-serif;color:#12294a">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px">
<table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden">
<tr><td style="background:#0a2a5c;color:#fff;padding:24px 28px">
<div style="font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#8fd3e8"><?= esc($ev['edicion']) ?></div>
<div style="font-size:22px;font-weight:bold;margin-top:4px"><?= esc($ev['nombre']) ?></div></td></tr>
<tr><td style="padding:28px">
<p style="margin:0 0 12px">Hola <strong><?= esc($ins['nombre']) ?></strong>,</p>
<p style="margin:0 0 16px;line-height:1.5">Recibimos tu solicitud de inscripción. Tu cupo se confirma cuando verifiquemos tu pago.</p>
<table width="100%" cellpadding="8" cellspacing="0" style="background:#eef6fc;border-radius:12px;font-size:14px">
<tr><td>Código de inscripción</td><td align="right"><strong><?= esc($codigo) ?></strong></td></tr>
<tr><td>Categoría</td><td align="right"><?= esc($ins['categoria']) ?></td></tr>
<tr><td>Monto</td><td align="right"><strong><?= esc($precio) ?></strong></td></tr>
<tr><td>Forma de pago elegida</td><td align="right"><?= esc($ins['forma_pago']) ?></td></tr>
</table>
<p style="margin:20px 0 8px;font-weight:bold">Siguiente paso: tu pago por <?= esc($ins['forma_pago']) ?></p>
<?php if (($instrucciones ?? '') !== '') : ?>
<p style="margin:0;line-height:1.5"><?= nl2br(esc($instrucciones)) ?></p>
<?php else : ?>
<p style="margin:0;line-height:1.5">Realiza tu pago y envía el comprobante, junto con tu código, a <a href="mailto:<?= esc($contacto, 'attr') ?>" style="color:#0e6b5c"><?= esc($contacto) ?></a>.</p>
<?php endif; ?>
<p style="margin:12px 0 0;line-height:1.5">Con tu comprobante emitiremos tu recibo y confirmaremos tu inscripción. Guarda este correo: tu código es <strong><?= esc($codigo) ?></strong>.</p>
<p style="margin:20px 0 0;font-size:13px;color:#5a6b85"><?= esc($ev['sede']) ?> · 17 al 19 de junio de 2027</p>
</td></tr></table></td></tr></table></body></html>

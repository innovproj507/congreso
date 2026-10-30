<?php

namespace Modules\Identidad\Services;

// Registro de auditoría (solo inserción). Se llama desde cualquier módulo en acciones sensibles.
class Auditoria
{
    public static function registrar(string $accion, ?string $entidad = null, string|int|null $entidadId = null, array $detalle = []): void
    {
        try {
            db_connect()->table('auditoria')->insert([
                'usuario_id' => session('usuario')['id'] ?? null,
                'accion'     => $accion,
                'entidad'    => $entidad,
                'entidad_id' => $entidadId === null ? null : (string) $entidadId,
                'detalle'    => $detalle ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null,
                'ip'         => service('request')->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo registrar auditoría: ' . $e->getMessage());
        }
    }
}

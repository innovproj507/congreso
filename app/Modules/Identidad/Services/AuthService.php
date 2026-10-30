<?php

namespace Modules\Identidad\Services;

use Modules\Identidad\Models\UsuarioModel;

// Login de usuarios internos con bloqueo temporal tras intentos fallidos.
class AuthService
{
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_MIN  = 15;

    public function __construct(private ?UsuarioModel $usuarios = null)
    {
        $this->usuarios ??= new UsuarioModel();
    }

    /** @return array{ok:bool,error?:string} */
    public function intentar(string $correo, string $clave): array
    {
        $generico = ['ok' => false, 'error' => 'Correo o contraseña incorrectos.'];
        $u        = $this->usuarios->where('correo', mb_strtolower(trim($correo)))->first();

        if (! $u || ! (int) $u['activo']) {
            password_verify($clave, '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyzABCDE'); // iguala el tiempo de respuesta
            Auditoria::registrar('login.fallido', 'usuario', null, ['correo' => mb_substr($correo, 0, 150)]);

            return $generico;
        }
        if ($u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time()) {
            return ['ok' => false, 'error' => 'Cuenta bloqueada temporalmente por intentos fallidos. Intenta más tarde.'];
        }

        if (! password_verify($clave, $u['password_hash'])) {
            $n   = (int) $u['intentos_fallidos'] + 1;
            $upd = ['intentos_fallidos' => $n];
            if ($n >= self::MAX_INTENTOS) {
                $upd = ['intentos_fallidos' => 0, 'bloqueado_hasta' => date('Y-m-d H:i:s', time() + self::BLOQUEO_MIN * 60)];
            }
            $this->usuarios->update($u['id'], $upd);
            Auditoria::registrar('login.fallido', 'usuario', $u['id']);

            return $generico;
        }

        $this->usuarios->update($u['id'], ['intentos_fallidos' => 0, 'bloqueado_hasta' => null, 'ultimo_acceso' => date('Y-m-d H:i:s')]);
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            $this->usuarios->update($u['id'], ['password_hash' => password_hash($clave, PASSWORD_DEFAULT)]);
        }

        $sesion = session();
        $sesion->regenerate(true); // evita fijación de sesión
        $sesion->set('usuario', ['id' => (int) $u['id'], 'nombre' => $u['nombre'], 'correo' => $u['correo'], 'rol' => $u['rol']]);
        Auditoria::registrar('login.ok', 'usuario', $u['id']);

        return ['ok' => true];
    }

    public function salir(): void
    {
        Auditoria::registrar('logout', 'usuario', session('usuario')['id'] ?? null);
        session()->destroy();
    }
}

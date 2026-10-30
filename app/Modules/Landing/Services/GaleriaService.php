<?php

namespace Modules\Landing\Services;

/**
 * Galería dinámica: lo que haya en `spp/galeria/` (fotos y un video) es lo que se publica.
 *
 * - Originales:  ROOTPATH/spp/galeria/   (jpg, jpeg, png, webp, mp4, webm, mov)
 * - Publicado:   public/img/galeria/     (WebP optimizado, miniaturas, video, poster y galeria.json)
 * - Títulos opcionales: spp/galeria/titulos.json  →  {"nombre-del-archivo.jpg": "Texto de la foto"}
 * - Orden: por nombre del archivo (natural). Para fijarlo, prefija con números: 01-apertura.jpg, 02-mesa.jpg
 *
 * En desarrollo se sincroniza sola al abrir la página; en producción se ejecuta `php spark galeria:procesar`.
 */
class GaleriaService
{
    private const LADO_GRANDE = 1600;
    private const LADO_MINIATURA = 640;
    private const EXT_FOTO = ['jpg', 'jpeg', 'png', 'webp'];
    private const EXT_VIDEO = ['mp4', 'webm', 'mov'];

    private string $fuente;
    private string $salida;

    public function __construct(?string $fuente = null, ?string $salida = null)
    {
        $this->fuente = rtrim($fuente ?? ROOTPATH . 'spp/galeria', '/\\');
        $this->salida = rtrim($salida ?? FCPATH . 'img/galeria', '/\\');
    }

    /** Datos listos para la vista. */
    public function obtener(): array
    {
        if (ENVIRONMENT === 'development') {
            try {
                $this->sincronizar();
            } catch (\Throwable $e) {
                log_message('error', 'Galería: no se pudo sincronizar: ' . $e->getMessage());
            }
        }
        $m = $this->leerManifiesto();

        return ['fotos' => $m['fotos'] ?? [], 'video' => $m['video'] ?? null];
    }

    /**
     * Regenera lo publicado si cambió algo en la carpeta de originales.
     *
     * @return array{cambios:bool,fotos:int,video:bool,mensajes:list<string>}
     */
    public function sincronizar(bool $forzar = false): array
    {
        $mensajes = [];
        if (! is_dir($this->fuente)) {
            return ['cambios' => false, 'fotos' => 0, 'video' => false, 'mensajes' => ['No existe la carpeta ' . $this->fuente]];
        }
        $titulos = $this->leerTitulos();
        [$fotos, $video] = $this->listarOriginales();
        $firma = $this->firma($fotos, $video, $titulos);

        $actual = $this->leerManifiesto();
        if (! $forzar && ($actual['firma'] ?? null) === $firma) {
            return ['cambios' => false, 'fotos' => count($actual['fotos'] ?? []), 'video' => ! empty($actual['video']), 'mensajes' => ['Sin cambios.']];
        }

        if (! is_dir($this->salida) && ! mkdir($this->salida, 0775, true) && ! is_dir($this->salida)) {
            throw new \RuntimeException('No se pudo crear ' . $this->salida);
        }

        $publicadas = [];
        $conservar  = [];
        foreach ($fotos as $i => $ruta) {
            $nombre = basename($ruta);
            $hash   = substr(sha1_file($ruta), 0, 10);
            $grande = "g-{$hash}.webp";
            $mini   = "g-{$hash}-t.webp";
            $conservar[] = $grande;
            $conservar[] = $mini;

            $dim = null;
            if (! is_file("{$this->salida}/{$grande}") || ! is_file("{$this->salida}/{$mini}") || $forzar) {
                $dim = $this->procesarFoto($ruta, "{$this->salida}/{$grande}", "{$this->salida}/{$mini}");
                if ($dim === null) {
                    $mensajes[] = "No se pudo procesar {$nombre}; se omite.";

                    continue;
                }
            }
            $dim ??= $this->dimensiones("{$this->salida}/{$grande}");
            if ($dim === null) {
                continue;
            }
            $publicadas[] = [
                'id'     => count($publicadas) + 1,
                'src'    => "img/galeria/{$grande}",
                'thumb'  => "img/galeria/{$mini}",
                'width'  => $dim[0],
                'height' => $dim[1],
                'alt'    => trim((string) ($titulos[$nombre] ?? '')) ?: 'Fotografía ' . (count($publicadas) + 1) . ' del Congreso Nacional de Pediatría 2026',
                'titulo' => trim((string) ($titulos[$nombre] ?? '')),
            ];
        }

        $videoPublicado = null;
        if ($video !== null) {
            $videoPublicado = $this->procesarVideo($video, $conservar, $mensajes, $titulos, $publicadas[0]['thumb'] ?? null);
        }

        $this->limpiarSobrantes($conservar);
        $manifiesto = ['firma' => $firma, 'generado' => date('c'), 'fotos' => $publicadas, 'video' => $videoPublicado];
        file_put_contents($this->salida . '/galeria.json', json_encode($manifiesto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $mensajes[] = count($publicadas) . ' foto(s)' . ($videoPublicado ? ' y 1 video' : '') . ' publicadas.';

        return ['cambios' => true, 'fotos' => count($publicadas), 'video' => $videoPublicado !== null, 'mensajes' => $mensajes];
    }

    // ---------------------------------------------------------------- lectura

    private function leerManifiesto(): array
    {
        $f = $this->salida . '/galeria.json';
        if (! is_file($f)) {
            return [];
        }
        $j = json_decode((string) file_get_contents($f), true);

        return is_array($j) && isset($j['fotos']) ? $j : [];
    }

    private function leerTitulos(): array
    {
        $f = $this->fuente . '/titulos.json';
        $j = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;

        return is_array($j) ? $j : [];
    }

    /** @return array{0:list<string>,1:?string} fotos ordenadas y el primer video */
    private function listarOriginales(): array
    {
        $fotos = [];
        $video = null;
        foreach (scandir($this->fuente) ?: [] as $nombre) {
            $ruta = $this->fuente . '/' . $nombre;
            if (! is_file($ruta)) {
                continue;
            }
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (in_array($ext, self::EXT_FOTO, true)) {
                $fotos[] = $ruta;
            } elseif (in_array($ext, self::EXT_VIDEO, true) && $video === null) {
                $video = $ruta;
            }
        }
        // Orden natural; "foto (2).jpg" va después de "foto.jpg" (así numera WhatsApp)
        usort($fotos, static function (string $a, string $b): int {
            $clave = static function (string $ruta): array {
                $n = pathinfo($ruta, PATHINFO_FILENAME);
                if (preg_match('/^(.*?)\s*\((\d+)\)$/', $n, $m)) {
                    return [mb_strtolower($m[1]), (int) $m[2]];
                }

                return [mb_strtolower($n), 0];
            };
            [$ba, $na] = $clave($a);
            [$bb, $nb] = $clave($b);

            return strnatcmp($ba, $bb) ?: $na <=> $nb;
        });

        return [$fotos, $video];
    }

    private function firma(array $fotos, ?string $video, array $titulos): string
    {
        $partes = [json_encode($titulos)];
        foreach (array_merge($fotos, $video ? [$video] : []) as $r) {
            $partes[] = basename($r) . '|' . filesize($r) . '|' . filemtime($r);
        }

        return sha1(implode("\n", $partes));
    }

    // ---------------------------------------------------------------- imágenes

    /** @return array{0:int,1:int}|null ancho y alto de la versión grande */
    private function procesarFoto(string $origen, string $grande, string $mini): ?array
    {
        $img = $this->abrir($origen);
        if (! $img) {
            return null;
        }
        $img = $this->orientar($img, $origen);
        $w   = imagesx($img);
        $h   = imagesy($img);

        $g = $this->escalar($img, $w, $h, self::LADO_GRANDE);
        imagewebp($g, $grande, 82);
        $dim = [imagesx($g), imagesy($g)];
        $t   = $this->escalar($img, $w, $h, self::LADO_MINIATURA);
        imagewebp($t, $mini, 76);

        return $dim;
    }

    private function abrir(string $ruta): ?\GdImage
    {
        $d = match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($ruta),
            'png'         => @imagecreatefrompng($ruta),
            'webp'        => @imagecreatefromwebp($ruta),
            default       => false,
        };

        return $d ?: null;
    }

    private function orientar(\GdImage $img, string $ruta): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg'], true)) {
            return $img;
        }
        $exif = @exif_read_data($ruta);
        $giro = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180, 6 => -90, 8 => 90, default => 0,
        };

        return $giro ? (imagerotate($img, $giro, 0) ?: $img) : $img;
    }

    private function escalar(\GdImage $img, int $w, int $h, int $lado): \GdImage
    {
        $f = min(1.0, $lado / max($w, $h)); // nunca se agranda
        $nw = max(1, (int) round($w * $f));
        $nh = max(1, (int) round($h * $f));
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return $dst;
    }

    private function dimensiones(string $ruta): ?array
    {
        $i = @getimagesize($ruta);

        return $i ? [$i[0], $i[1]] : null;
    }

    // ---------------------------------------------------------------- video

    private function procesarVideo(string $origen, array &$conservar, array &$mensajes, array $titulos, ?string $respaldoPoster): ?array
    {
        $hash   = substr(sha1_file($origen), 0, 10);
        $ext    = strtolower(pathinfo($origen, PATHINFO_EXTENSION));
        $destino = "v-{$hash}.{$ext}";
        $poster = "v-{$hash}-poster.jpg";
        $conservar[] = $destino;
        $conservar[] = $poster;

        if (! is_file("{$this->salida}/{$destino}") && ! copy($origen, "{$this->salida}/{$destino}")) {
            $mensajes[] = 'No se pudo copiar el video.';

            return null;
        }
        if (! is_file("{$this->salida}/{$poster}")) {
            $this->extraerPoster($origen, "{$this->salida}/{$poster}");
        }
        $dim = $this->dimensiones("{$this->salida}/{$poster}");
        if ($dim === null) {
            $mensajes[] = 'Sin ffmpeg no se pudo crear la portada del video; se usa una foto como portada.';
        }

        return [
            'src'    => "img/galeria/{$destino}",
            'tipo'   => $ext === 'webm' ? 'video/webm' : ($ext === 'mov' ? 'video/quicktime' : 'video/mp4'),
            'poster' => $dim ? "img/galeria/{$poster}" : $respaldoPoster,
            'width'  => $dim[0] ?? 640,
            'height' => $dim[1] ?? 360,
            'titulo' => trim((string) ($titulos[basename($origen)] ?? '')) ?: 'Video oficial del Congreso 2026',
        ];
    }

    private function extraerPoster(string $video, string $destino): void
    {
        if (! function_exists('exec')) {
            return;
        }
        $cmd = 'ffmpeg -y -loglevel error -ss 1 -i ' . escapeshellarg($video) . ' -frames:v 1 -vf scale=720:-2 -q:v 3 ' . escapeshellarg($destino) . ' 2>&1';
        @exec($cmd);
    }

    // ---------------------------------------------------------------- limpieza

    /** Borra lo generado antes que ya no corresponde a ningún original (fotos quitadas o cambiadas). */
    private function limpiarSobrantes(array $conservar): void
    {
        foreach (glob($this->salida . '/{g-*.webp,v-*}', GLOB_BRACE) ?: [] as $f) {
            if (is_file($f) && ! in_array(basename($f), $conservar, true)) {
                @unlink($f);
            }
        }
    }
}

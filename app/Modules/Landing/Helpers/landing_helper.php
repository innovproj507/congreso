<?php

if (! function_exists('icono')) {
    // Ícono lucide en línea (trazo). Decorativo: aria-hidden.
    function icono(string $nombre, int $size = 24, string $class = ''): string
    {
        static $mapa = null;
        $mapa ??= require APPPATH . 'Modules/Landing/Config/Icons.php';
        $svg = $mapa[$nombre] ?? '';

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="' . esc($class, 'attr') . '">' . $svg . '</svg>';
    }
}

if (! function_exists('seccion_inicio')) {
    // Medidas de Fexo: sección 120px, sub-título 20px uppercase bold, h2 52px/500
    function seccion_inicio(string $id, string $eyebrow, string $titulo, bool $dark = false, bool $tint = false): string
    {
        $bg   = $dark ? 'fondo-degradado text-white' : ($tint ? 'bg-esm-50' : 'bg-white');
        $ey   = $dark ? 'text-white/80' : 'text-esm-700';

        return '<section id="' . esc($id, 'attr') . '" class="py-20 lg:py-[120px] ' . $bg . '"><div class="mx-auto max-w-[1320px] px-5">'
            . '<div class="reveal mb-12 text-center lg:mb-14"><p class="font-display text-base font-bold uppercase leading-none sm:text-xl ' . $ey . '">' . esc($eyebrow) . '</p>'
            . '<h2 class="font-display mx-auto mt-3 max-w-3xl text-[26px] font-medium leading-[1.4] sm:text-[40px] sm:leading-[55px] xl:text-[52px] xl:leading-[1.25]">' . esc($titulo) . '</h2></div>';
    }
}

if (! function_exists('seccion_fin')) {
    function seccion_fin(): string
    {
        return '</div></section>';
    }
}

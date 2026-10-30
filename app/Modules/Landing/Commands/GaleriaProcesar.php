<?php

namespace Modules\Landing\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Landing\Services\GaleriaService;

class GaleriaProcesar extends BaseCommand
{
    protected $group       = 'Congreso';
    protected $name        = 'galeria:procesar';
    protected $description = 'Optimiza las fotos y el video de spp/galeria y actualiza la galería de la landing.';
    protected $usage       = 'galeria:procesar [--forzar]';
    protected $options     = ['--forzar' => 'Regenera todo aunque no haya cambios.'];

    public function run(array $params)
    {
        $forzar = array_key_exists('forzar', $params) || CLI::getOption('forzar') !== null;
        $r      = (new GaleriaService())->sincronizar($forzar);
        foreach ($r['mensajes'] as $m) {
            CLI::write($m, $r['cambios'] ? 'green' : 'yellow');
        }
        CLI::write(sprintf('Galería: %d foto(s)%s.', $r['fotos'], $r['video'] ? ' + video' : ''));
    }
}

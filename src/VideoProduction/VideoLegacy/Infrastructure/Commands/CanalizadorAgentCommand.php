<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Infrastructure\Commands;

use Canalizador\VideoProduction\VideoLegacy\Infrastructure\Agents\AudioTranscriptor;
use Canalizador\VideoProduction\VideoLegacy\Infrastructure\Agents\CartoonVideoMaker;
use Illuminate\Console\Command;

class CanalizadorAgentCommand extends Command
{
    protected $signature   = 'canalizador:agent';
    protected $description = 'Execute the Canalizador video agent command';

    public function handle(
        CartoonVideoMaker $cartoonVideoMaker
    ): void {
        $response = $cartoonVideoMaker->execute('Hola me puedes generar un video de dibujos animados este video? 2V2M-la_4RI')->asStream();

        foreach ($response as $chunk) {
            echo $chunk->text;
        };
    }
}

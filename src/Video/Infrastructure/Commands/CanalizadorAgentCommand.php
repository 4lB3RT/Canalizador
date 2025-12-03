<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Commands;

use Canalizador\Video\Infrastructure\Agents\AudioTranscriptor;
use Canalizador\Video\Infrastructure\Agents\CartoonVideoMaker;
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

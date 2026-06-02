<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Infrastructure\Commands;

use Helmreel\VideoProduction\VideoLegacy\Infrastructure\Agents\AudioTranscriptor;
use Helmreel\VideoProduction\VideoLegacy\Infrastructure\Agents\CartoonVideoMaker;
use Illuminate\Console\Command;

class HelmreelAgentCommand extends Command
{
    protected $signature   = 'helmreel:agent';
    protected $description = 'Execute the Helmreel video agent command';

    public function handle(
        CartoonVideoMaker $cartoonVideoMaker
    ): void {
        $response = $cartoonVideoMaker->execute('Hola me puedes generar un video de dibujos animados este video? 2V2M-la_4RI')->asStream();

        foreach ($response as $chunk) {
            echo $chunk->text;
        };
    }
}

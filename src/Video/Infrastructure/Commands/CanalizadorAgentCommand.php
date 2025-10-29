<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Commands;

use Canalizador\Video\Infrastructure\Agents\AudioTranscriptor;
use Illuminate\Console\Command;

class CanalizadorAgentCommand extends Command
{
    protected $signature   = 'canalizador:agent';
    protected $description = 'Execute the Canalizador video agent command';

    public function handle(
        AudioTranscriptor $audioTranscriptor
    ): void {
        $response = $audioTranscriptor->execute('Hola me puedes transcribir este video? 2V2M-la_4RI')->asStream();

        foreach ($response as $chunk) {
            echo $chunk->text;
        };
    }
}

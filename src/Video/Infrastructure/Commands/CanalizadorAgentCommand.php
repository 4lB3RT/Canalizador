<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure;

use Canalizador\Video\Infrastructure\Agents\AudioTranscriptor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use function Laravel\Prompts\textarea;

class CanalizadorAgentCommand extends Command
{
    protected $signature = 'canalizador:agent';
    protected $description = 'Execute the Canalizador video agent command';

    public function handle(
        AudioTranscriptor $audioTranscriptor
    ): void
    {
        /*
        Redis::set('message', textarea('Promp:'));
        $message = Redis::get('message');
        */

        $response = $audioTranscriptor->execute(textarea('Promp:'))->asStream();

        foreach ($response as $chunk) {
            echo $chunk->text;
        };
    }
}

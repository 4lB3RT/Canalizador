<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Video\Infrastructure\Agents\AudioTranscriptor;
use Canalizador\YouTube\Video\Infrastructure\Agents\CartoonVideoMaker;
use Illuminate\Console\Command;

class VideoAgentCommand extends Command
{
    protected $signature   = 'youtube:agent {agent=cartoon : Agent to run (cartoon|transcriptor)}';
    protected $description = 'Execute a YouTube video agent';

    public function handle(
        CartoonVideoMaker $cartoonVideoMaker,
        AudioTranscriptor $audioTranscriptor,
    ): int {
        $agent   = $this->argument('agent');
        $videoId = $this->ask('Enter the YouTube video ID');

        $response = match ($agent) {
            'cartoon'      => $cartoonVideoMaker->execute($videoId)->asStream(),
            'transcriptor' => $audioTranscriptor->execute($videoId)->asStream(),
            default        => null,
        };

        if ($response === null) {
            $this->error("Unknown agent: {$agent}. Use 'cartoon' or 'transcriptor'.");
            return self::FAILURE;
        }

        foreach ($response as $chunk) {
            echo $chunk->text;
        }

        return self::SUCCESS;
    }
}

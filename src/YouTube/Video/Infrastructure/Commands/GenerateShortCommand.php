<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Illuminate\Console\Command;

class GenerateShortCommand extends Command
{
    protected $signature   = 'youtube:generate-short {videoYoutubeId : The YouTube video platform ID}';
    protected $description = 'Generate shorts from a YouTube video';

    public function handle(GenerateShort $generateShort): int
    {
        $videoYoutubeId = $this->argument('videoYoutubeId');

        $generateShort->execute(
            new GenerateShortRequest(videoId: $videoYoutubeId)
        );

        $this->info('Shorts generated successfully.');

        return self::SUCCESS;
    }
}

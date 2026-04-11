<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShorts;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShortsRequest;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Illuminate\Console\Command;

class GenerateShortsCommand extends Command
{
    protected $signature   = 'youtube:generate-shorts {videoYoutubeId : The YouTube video platform ID}';
    protected $description = 'Generate and publish shorts from a YouTube video';

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     */
    public function handle(GenerateShorts $generateShorts): int
    {
        $videoYoutubeId = $this->argument('videoYoutubeId');

        $response = $generateShorts->execute(
            new GenerateShortsRequest(videoYoutubeId: $videoYoutubeId)
        );

        $this->info('Published shorts: ' . implode(', ', $response->publishedShortIds));

        return self::SUCCESS;
    }
}

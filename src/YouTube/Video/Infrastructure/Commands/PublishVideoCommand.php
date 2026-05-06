<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideoRequest;
use Illuminate\Console\Command;

class PublishVideoCommand extends Command
{
    protected $signature   = 'youtube:publish-video {videoId : The internal short ID to publish}';
    protected $description = 'Schedule and publish a short to the target platform';

    public function handle(PublishVideo $publishVideo): int
    {
        $response = $publishVideo->execute(
            new PublishVideoRequest(
                videoId:  $this->argument('videoId'),
                platform: 'youtube',
            )
        );

        $this->info(sprintf(
            'Short scheduled at %s on %s (platform id: %s, url: %s).',
            $response->scheduledAt,
            $response->platform,
            $response->platformVideoId ?: 'n/a',
            $response->platformUrl     ?: 'n/a',
        ));

        return self::SUCCESS;
    }
}

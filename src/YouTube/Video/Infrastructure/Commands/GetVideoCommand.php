<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Commands;

use Canalizador\YouTube\Video\Application\UseCases\GetVideo\GetVideo;
use Canalizador\YouTube\Video\Application\UseCases\GetVideo\GetVideoRequest;
use Illuminate\Console\Command;

class GetVideoCommand extends Command
{
    protected $signature   = 'youtube:get-video {videoId : The YouTube video platform ID}';
    protected $description = 'Download a YouTube video, extract audio, transcribe and persist it locally';

    public function handle(GetVideo $getVideo): int
    {
        $response = $getVideo->execute(
            new GetVideoRequest(videoId: $this->argument('videoId'))
        );

        $video = $response->toArray();

        $this->info(sprintf(
            'Video stored: id=%s title=%s',
            $video['id'],
            $video['title'],
        ));

        return self::SUCCESS;
    }
}

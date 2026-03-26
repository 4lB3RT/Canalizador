<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Tools;

use Canalizador\YouTube\Video\Domain\Repositories\VideoDownloader as VideoDownloaderRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;
use Prism\Prism\Tool;

final class VideoDownloader extends Tool
{
    public function __construct(
        private readonly VideoDownloaderRepository $videoDownloader,
    ) {
        parent::__construct();
        $this->as('VideoDownloader')
            ->for('Download a YouTube video given its ID and return the local file path of the downloaded video.')
            ->withStringParameter('videoId', 'The YouTube video ID.')
            ->using($this);
    }

    public function __invoke(string $videoId): string
    {
        $localPath = $this->videoDownloader->download(new YouTubeVideoId($videoId));

        return $localPath->value();
    }
}
